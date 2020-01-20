# PinguInstaller

Provides with an interface to install Pingu.

Pingu is considered installed when the file storage/installed exists. This file will contain the timestamp at the date of installation

This package will only check requirements and gather data to write an .env file. It will then run all module migrations which are responsible for the actual installation. This does not write anything in database.