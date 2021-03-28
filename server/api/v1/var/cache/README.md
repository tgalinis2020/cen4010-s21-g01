Normally cache files shouldn't be committed to version control, but in this case
it is necessary since the target host server does not provide write access for the
apache "user" and this project uses Git for deployment.

Make sure to regenerate these files in the event of an update in routing,
dependencies, or resource graph configuration.