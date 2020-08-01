# JobUI
For some reasons I made the permissions to "op" to prevent earning money in a "Build and Mine Protected World" like Lobby.
You will learn how to let players use /job command and earn money in a specified world, but before you need to install the PurePerms plugin.
### Dependencies:
* [FormAPI by jojoe77777](https://github.com/jojoe77777/FormAPI) (Necessary)
* [PurePerms by 64FF00](https://github.com/poggit-orphanage/PurePerms) (Optional)
### How to let players use /job command and earn money in a specified world?
As I said you need to install the PurePerms plugin. Then in the `plugin_data` folder find the `PurePerms` and open `groups.yml`.
### Permissions and Commands:
Permission | Command | Default | About
---------- | ------- | ------- | -----
jobui.command.job | /job | op | Able to see the UI of the Jobs
jobui.command.retire | /retire | true | Able to be retired 
jobui.earn.break | - | op | Able to earn money of a breaking Job
jobui.earn.place | - | op | Able to earn money of a placing Job
* As you see there is an independent /retire command. Although there is an ability to get retired through the Form, I've added /retire command to let players get retired when they don't have permission to use /job.
