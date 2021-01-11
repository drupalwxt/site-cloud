include .env
NAME := $(or $(BASE_IMAGE),$(BASE_IMAGE),drupalwxt/site-cloud)
VERSION := $(or $(VERSION),$(VERSION),'latest')
PLATFORM := $(shell uname -s)
$(eval GIT_USERNAME := $(if $(GIT_USERNAME),$(GIT_USERNAME),gitlab-ci-token))
$(eval GIT_PASSWORD := $(if $(GIT_PASSWORD),$(GIT_PASSWORD),$(CI_JOB_TOKEN)))
DOCKER_REPO := https://github.com/drupalwxt/docker-scaffold.git
GET_DOCKER := $(shell [ -d docker ] || git clone --branch 9.x-postgres $(DOCKER_REPO) docker && patch -p1 < docker/patches/custom-mounts.patch)
include docker/Makefile
