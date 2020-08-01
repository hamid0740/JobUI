# JobUI
* I changed the permissions to "op" to prevent earning money in a "Build and Mine Protected World" like Lobby.
* You will learn how to let players use /job command and earn money in a specified world, but before you need to install the PurePerms plugin.
### Dependencies:
* [FormAPI by jojoe77777]() [[Download]]() [[GitHub]](https://github.com/jojoe77777/FormAPI) __(Necessary)__
* [EconomyAPI by onebone](https://poggit.pmmp.io/p/EconomyAPI/) [[Download]]() [[GitHub]](https://github.com/poggit-orphanage/EconomyS/) __(Necessary)__
* [PurePerms by 64FF00]() [[Download]]() [[GitHub]](https://github.com/poggit-orphanage/PurePerms) (Optional)
### How to let players use /job command and earn money in a specified world?
* As I said you need to install the PurePerms plugin.
* __*Before completing the next steps, make sure that `enable-multiworld-perms` has been set to `true` in `plugin_data\PurePerms\config.yml`.*__
* Open this file path `plugin_data\PurePerms\groups.yml`. Then the only thing that you should do is to add the world and permissions to the Group you want. I will give an example below:
```yaml
---
Guest:
  alias: gst
  isDefault: true
  inheritance: []
  permissions:
  - jobui.command.retire
  worlds:
    Mine:
      isDefault: false
      permissions:
      - jobui.command.job
      - jobui.earn.break
      - jobui.earn.place
...
```
* If a player is in the Guest group, he won't be able to use /job command in every world except the "Mine"
* Also, he won't be able to earn money by having a job in every world except the "Mine"
### Permissions and Commands:
Permission | Command | Default | About
---------- | ------- | ------- | -----
jobui.command.job | /job | op | Able to see the UI of the Jobs
jobui.command.retire | /retire | true | Able to be retired 
jobui.earn.break | - | op | Able to earn money of a breaking Job
jobui.earn.place | - | op | Able to earn money of a placing Job
* As you see, there is an independent /retire command. Although there is a button to get retired through the Form, I've added /retire command to let players get retired when they don't have permission to use /job.
