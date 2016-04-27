# EStudio-Survey-Web-Files

Features of the Survey Site:

- Site requires login at https://www.cs.uky.edu/~anwh223/estudio/Login.php
  - Login brings you to the control panel where you can manage/create surveys and retrieve their results and manage/invite new users
  - You may reset you password by following the link on the login page and entering your username email address
  - You will then receive an email with a link to reset your password.


- You can manage current users and invite new ones
  - To manage users click on "Manage Users" in the control panel
  - There are currently 3 user levels SuperAdmin, Admin, and Staff

  - Each user level can only manage user levels below their own user level
    - Managing users involves either promoting/demoting the users level or deleting the user
    - A user can promote other users below their own level to their level and demote other users below their own level to levels below their own level

- New users can be invited to register
  - You may only invite users at or below your user level
  - To invite a new user click the "Add User" button, fill out the email you wish to send the invitation to, select the user level for the new user, and click "Invite to Register"
  - An email with a link to register will be sent to that email.  Clicking on the link will bring you to a page where you can enter a username and password
  - Passwords are stored securely using modern standards (pbkdf2 iterative hashing)
  - If you successfully registered you will receive a popup and be redirected to the login screen
  - You receive an email when users you invite have registered

- You can manage and create surveys
  - To manage surveys simply click the survey's name in the control panel
  - You can select date ranges to retrieve results from
  - You can retrieve all results
  - You can preview a survey
  - You can deactivate an active survey
  - You can set a survey as the active survey
  - You can delete a survey and all it's results

- To create a new survey, click "Create New Survey" on the control panel page.
  - Give the survey a name
  - Add any number of questions (up to 50) to the survey
  - Then click "Add Survey" to add it to your list of surveys
  - The survey will not be active by default, you will have to set it as the active survey from the control panel

- Surveys can be completed at https://www.cs.uky.edu/~anwh223/estudio/Survey.php
