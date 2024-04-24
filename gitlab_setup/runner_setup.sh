# Runner Creation on Ubuntu 22.04:

# Create the docker volume for the config files
docker volume create gitlab-runner-config

# Run the runner
docker run -d --name gitlab-runner --restart unless-stopped \
    -v /var/run/docker.sock:/var/run/docker.sock \
    -v gitlab-runner-config:/etc/gitlab-runner \
    gitlab/gitlab-runner:latest

# Register the runner
docker run --rm -it -v gitlab-runner-config:/etc/gitlab-runner gitlab/gitlab-runner:latest register
# https://gitlab.com/ # gitlab web
# [paste token] # gitlab token
# docker:20.10.21 # default docker image

# Edit config.toml to set priviledged = true and volumes = ["/certs/client", "/cache"]
docker run --rm -it -v gitlab-runner-config:/etc/gitlab-runner ubuntu:latest /bin/bash
apt update
apt install vim -y
vim /etc/gitlab-runner/config.toml
# Save and exit

# Restart runner to get new config
docker restart gitlab-runner
