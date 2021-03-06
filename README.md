# Team 0xC0FFEE Project Repository

## Course

CEN4010 Principles of Software Engineering  
Florida Atlantic University  
Dr. Shihong Huang - Professor  
Nataliia Neshenko - Teacher's Assistant  
Spring 2021


## Members

Lindsey Butts  
Garry Florestal  
Thomas Galinis  
Lenora Gray  
Kevin Toyloy  


## Development

If you haven't done so already, please install [Node.js](https://nodejs.org/en/download/).
This project contains third-party dependencies. After cloning it, run the
following command to initialize your local copy of this repository:

```console
$ npm run init
```

If multiple people are working on the same branch, don't forget to `git pull`
before you begin your work! Depending on the amount of work, it may be in your
best interest to make a new branch and merge it later.


## Deployment

Work pushed into this repository will not be immediately reflected in FAU's end.
To update the web application with the latest changes, SSH into the LAMP server
via putty in VMware and run the following command in the account's home directory
(should already be there immediately after logging in):

```console
[cen4010_s21_g01@lamp ~]$ git pull
```

Optionally, you can specify a branch. Make sure you've checked out the branch
before pulling changes!

```console
[cen4010_s21_g01@lamp ~]$ git pull origin feat-my-cool-feature
```

