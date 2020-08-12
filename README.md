# JobUI
[![](https://poggit.pmmp.io/shield.state/JobUI)](https://poggit.pmmp.io/p/JobUI) [![](https://poggit.pmmp.io/shield.api/JobUI)](https://poggit.pmmp.io/p/JobUI) [![](https://poggit.pmmp.io/shield.dl.total/JobUI)](https://poggit.pmmp.io/p/JobUI) [![](https://poggit.pmmp.io/shield.dl/JobUI)](https://poggit.pmmp.io/p/JobUI)
* __!!! IMPORTANT !!! if you have installed this plugin before, remove the `plugin_data/JobUI` folder then the new Yaml files will be created.__
* __Customisable and Infinite Jobs are supported. You can edit or add jobs in `plugin_data/JobUI/jobs.yml`.__
* __Customisable Messages are supported. You can change them in `plugin_data/JobUI/messages.yml`__
* I changed the default permissions to "op" to prevent earning money in a "Build and Mine Protected World" like Lobby.
* You will learn how to let players Join Jobs and Earn Money only in a specified world, but before you need to install the PurePerms plugin.
### Default Jobs
* __Tree-Cutter:__ By joining this job, when you break any kind of logs with any directions you will earn 25$.
* __Miner:__ By joining this job, when you break Stone you will earn 25$, when you break Coal Ore you will earn 30$ and when you break Iron Ore you will earn 35$.
* __Hunter:__ By joining this job, when you kill a Mob(Animal or Monster) you will earn 30$.
* __Murderer:__ By joining this job, when you kill a Player you will earn 50$.
### Dependencies:
* [EconomyAPI by onebone](https://poggit.pmmp.io/p/EconomyAPI/) [[Download]](https://poggit.pmmp.io/r/34531/EconomyAPI.phar) [[GitHub]](https://github.com/poggit-orphanage/EconomyS/) __(Required)__
* [PurePerms by 64FF00](https://poggit.pmmp.io/p/PurePerms/) [[Download]](https://poggit.pmmp.io/r/70018/PurePerms.phar) [[GitHub]](https://github.com/poggit-orphanage/PurePerms/) (Optional)
* [PureEntitiesX by RevivalPMMP](https://poggit.pmmp.io/p/PureEntitiesX) [[Download]](https://poggit.pmmp.io/r/93487/PureEntitiesX.phar) [[GitHub]](https://github.com/RevivalPMMP/PureEntitiesX/) (Optional)
### How to let players Join Jobs and Earn Money only in a specified world?
* As I said you need to install the PurePerms plugin.
* __*Before completing the next steps, make sure that `enable-multiworld-perms` has been set to `true` in `plugin_data/PurePerms/config.yml`.*__
* Open this file path `plugin_data/PurePerms/groups.yml`. Then the only thing that you should do is to add the world and permissions to the Group you want. I will give an example below:
```yaml
---
Guest:
  alias: gst
  isDefault: true
  inheritance: []
  permissions:
  worlds:
    Mine:
      isDefault: false
      permissions:
      - jobui.job.tree-cutter
      - jobui.job.miner
      - jobui.earn.break
    Survival:
      isDefault: false
      permissions:
      - jobui.job.hunter
      - jobui.earn.hunter
    PvP: 
      isDefault: false
      permissions:
      - jobui.job.murderer
      - jobui.earn.murderer
    
...
```
* If a player is in the Guest group, he will be able to join "Tree-Cutter" and "Miner" job only in the "Mine" world
* If a player is in the Guest group, he will be able to join "Hunter" job only in the "Survival" world
* If a player is in the Guest group, he will be able to join "Murderer" job only in the "PvP" world
* Also, he will be able to earn money by a "Breaking" MissionType only in the Mine
* Also, he will be able to earn money by a "Hunter" MissionType only in the "Survival"
* Also, he will be able to earn money by a "Murderer" MissionType only in the "PvP"
### Image
![Preview](https://www.mediafire.com/convkey/7ca9/muxd8cv9quuoo6vzg.jpg)
### To-Do list
* [X] Adding Customizable feature for jobs, so that you will be able to add more jobs
* [X] Adding Customizable feature for texts of the UI and every messages
* [X] Adding Customizable feature for Button names and Images for JobJoinUI
* [X] Adding "Hunter" & "Murderer" MissionType
* [ ] Adding Job Mode(Simple Mode and Goal Mode) (Coming in less than 3 days)
### Permissions and Commands:
Permission | Command | Default | About
---------- | ------- | ------- | -----
jobui.* | - | op | Able to use all commands, all earning ways and join all of the jobs of JobUI plugin
jobui.job.* | - | op | Able to join all of the jobs
jobui.command.job | /job | true | Able to see the UI of the Jobs
jobui.command.retire | /retire | true | Able to be retired 
jobui.earn.break | - | op | Able to earn money by a Breaking Job
jobui.earn.place | - | op | Able to earn money by a Placing Job
jobui.earn.hunter | - | op | Able to earn money by a Killing Mobs Job
jobui.earn.murderer | - | op | Able to earn money by a Killing Players Job
* You can edit each Job permission in `plugin_data/JobUI/jobs.yml`
