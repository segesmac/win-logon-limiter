param (
    $folder_of_tasks = $(Join-Path $PSScriptRoot 'build_tasks')
)

# Make sure scripts stop when they fail
$ErrorActionPreference = 'Stop'

# Need to put everything into a try-catch here so we can capture all the error output and output it to stdout correctly
try {
    Write-Output "Running tasks from $($PWD.Path)..."
    # Select all ps1 files in task folder that begin with more than one digit followed by a '-'
    # We'll ignore files named starting with anything other than at least 2 digits
    $task_files = $(Get-ChildItem $folder_of_tasks | Where-Object { $_.Name -match '^\d\d+p*-.*\.ps1$' } | Sort-Object Name | Select-Object FullName).FullName
    Write-Output "Found the following files to run:"
    Write-Output $task_files
    # We can also run tasks in parallel if we wish (circumventing the need for complicated yaml files)
    function read_parallel_jobs {
        # Let's get the output from all jobs running in parallel
        $jobs_unread = Get-Job -HasMoreData $true
        # While there are jobs that still have data
        While ($jobs_unread){
            foreach ($job in $jobs_unread){
                if (Get-Job $job.Name -HasMoreData $true){
                    # Job might "have more data", but if it's null, we don't want to output the header here
                    $job_output = Receive-Job $job.Name -Keep
                    if ($null -ne $job_output){
                        Write-Output "///////////////////////// Job $($job.Name) \\\\\\\\\\\\\\\\\\\\\\\\\"
                    }
                    Receive-Job $job.Name
                }
            }
            Start-Sleep -Seconds 5
            $jobs_unread = Get-Job -HasMoreData $true
        }
        # Now that jobs have more data to give out, remove them (and get any extra output we might have missed)
        foreach ($job in $jobs_unread) {
            Receive-Job $job -AutoRemoveJob -Wait
        }
    }

    # function for running parallel jobs
    function run_parallel_tasks {
        param ($parallel_task_file_list)
         # Run the parallel task files
         foreach ($parallel_task_file in $parallel_task_file_list){
            Start-Job -Name $parallel_task_file.Name -ScriptBlock {
                param (
                    $parallel_file_obj
                )
                Write-Output "-----------------Running parallel task $($parallel_file_obj.Name)"

                $stopwatch = [System.Diagnostics.Stopwatch]::new()
                $stopwatch.Start()
                # Dotsource task we'd like to run in parallel
                . $parallel_file_obj.FullName
                $stopwatch.Stop()
                $time_elapsed = $stopwatch.Elapsed

                $last_result = $LASTEXITCODE
                Write-Output "Last Exit Code: $last_result"

                Write-Output "------------------End parallel task $($parallel_file_obj.Name). Ran for $($time_elapsed.ToString('hh:mm:ss'))"

                if ($last_result -ne 0 -and $null -ne $last_result){
                    throw "$($parallel_file_obj.Name) FAILED with exit code $last_result"
                }
            } -ArgumentList $parallel_task_file
        }
    }
    # Create list of parallel task (job) files
    $parallel_task_list = @()
    # This prior_task variable is needed, because we will run multiple parallel tasks in sections
    # If a non-parallel task is followed by 4 parallel tasks followed by 2 non-parallel tasks, then 3 more parallel tasks, we will first run the 4
    # non-parallel task, then the 4 parallel tasks, then the 2 non-parallel tasks, then the 3 parallel tasks
    $prior_task_file_was_parallel = $false
    foreach ($task_file in $task_files){
        # Gather files that start with digits followed by a p- (###p-)
        if ($task_file.Name -match '^\d\d+p-.*\.ps1$'){
            $parallel_task_list += $task_file
            $prior_task_file_was_parallel = $true
        } elseif ($prior_task_file_was_parallel){
            # Run the parallel tasks
            run_parallel_tasks $parallel_task_list
            # Wait for the parallel tasks to finish and retrieve their output
            read_parallel_jobs
            # clear the list
            $parallel_task_list = @()
            $prior_task_file_was_parallel = $false
        } 
        if ($task_file.Name -match '^\d\d+-.*\.ps1$'){
            # Run sequential task
            Write-Output "-----------------Running task $($task_file.Name)"
            $stopwatch = [System.Diagnostics.Stopwatch]::new()
            $stopwatch.Start()
            # Dotsource task we'd like to run
            . $task_file.FullName
            $stopwatch.Stop()
            $time_elapsed = $stopwatch.Elapsed

            $last_result = $LASTEXITCODE
            Write-Output "Last Exit Code: $last_result"

            Write-Output "------------------End task $($task_file.Name). Ran for $($time_elapsed.ToString('hh:mm:ss'))"

            if ($last_result -ne 0 -and $null -ne $last_result){
                throw "$($task_file.Name) FAILED with exit code $last_result"
            }
        }

    }
    # It's possible there are no sequential tasks slated following the parallel tasks, so we need to run those after the for loop
    if ($prior_task_file_was_parallel){
        # Run the parallel tasks
        run_parallel_tasks $parallel_task_list
        # Wait for the parallel tasks to finish and retrieve their output
        read_parallel_jobs
        # clear the list
        $parallel_task_list = @()
        $prior_task_file_was_parallel = $false
    }
    Write-Output 'Tasks finished!'
    
} catch {
    Write-Output 'ERROR FOUND:'
    Write-Output $_.ScriptStackTrace
    Write-Output $_.Exception
    Write-Output $_.ErrorDetails
    throw "$($_.Exception) - SEE DETAILS ABOVE"
}