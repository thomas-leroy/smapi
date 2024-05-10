# Smapi

<p align="center">
   <img src="https://github.com/thomas-leroy/smapi/blob/main/logo.png?raw=true" width="200">
</p>

The objective of this project is to produce the simplest possible API to optimize and distribute images from a PHP environment.

This project gives you everything you need to run a local PHP 8 environment with Nginx using Docker. It includes a `Dockerfile`, an Nginx configuration file (`nginx.conf`) and a `Makefile` to simplify Docker commands.

Simple commands allow you to quickly launch the project locally or prepare the version ready to upload.

## Prerequisites

- Have Docker installed on your machine (<https://docs.docker.com/engine/install/>)
- Clone this repo `git clone git@github.com:thomas-leroy/smapi.git`

## Use

1. Open a terminal in the folder where these files are located.
2. Run `make init` to build the Docker image (on first use).
3. Run `make up` to start the container.
4. Go to `http://localhost:1234` to see your server in action.
5. Use `make down` to shut down the server when you're done.

## Where to store images?

All source images are stored in a subfolder by theme in `./src/images-sources/**/image.jpg`.

It is possible to create subfolder to store images in source images.

Subfolders are used in API calls.

Note: for the moment the script only works with one level of subfolder.

## Makefile commands

Commands are accessible to perform common project actions.

For example: `make up` to start the project.

### `make init`

This command builds a Docker image from the `Dockerfile`.

### `make up`

This command runs the Docker container from the image you created.

### `make down`

This command stops and deletes the currently running Docker container.

### `make shell`

Access the container's command line (CLI).

### `make bundle`

Prepare a `bundle` folder whose contents are to be placed in the online API directory (e.g. via your FTP or other solution).

## Available routes

### 1. Get Media Folders List

- **Endpoint:** `/folders`
- **Method:** `GET`
- **Summary:** Retrieves a list of media folders.
- **Responses:**
  - **200:** Success - Returns the list of media folders.

### 2. Get Images from Specific Folder

- **Endpoint:** `/images/{folderName}`
- **Method:** `GET`
- **Summary:** Fetches images from the specified folder.
- **Parameters:**
  - **folderName (path parameter):** The name of the folder from which to retrieve images. Must be a string of at least 1 character.
- **Responses:**
  - **200:** Success - Returns the images from the specified folder.

### 3. Get Swagger File Content (Development Only)

- **Endpoint:** `/swagger`
- **Method:** `GET`
- **Summary:** Provides the content of the Swagger file. This endpoint is intended for development purposes only.
- **Responses:**
  - **200:** Success - Returns the Swagger file content.

## CRONJOB: optimize and compress images

To synchronize and optimize images for the web, configure a cronjob (period to be defined) on the route:`http://localhost/cron-sync-and-optim.php`.

The script can take a long time to run for the first time and will timeout regularly. At each launch it will advance in its processing, until it has everything synchronized.

The images are to be stored in the ./src/images-sources folder, and will be copied optimized into the ./src/images-optim directory.
