# CiviCRM: Hide Activity Types
## com.joinery.hideactivitytypes

![Screenshot](/images/screenshot.png)

Hide activity types in one or more ways:

* Remove the activity type from the "Actions" list on the Activity Summary.
* On a per-contact-type basis: activity type will be removed from the "New Activity"
  list under a contact's Activities tab, based on the contact's Type or Sub-Type.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Usage

When editing an Activity Type, two new settings are provided by this extension (shown
in screenshot above):
* "Hide from Actions Menu?"
* "Hide for Contact Type(s)"

## Requirements

* PHP v7.0+
* CiviCRM 5.0

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.joinery.hideactivitytypes@https://github.com/FIXME/com.joinery.hideactivitytypes/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/com.joinery.hideactivitytypes.git
cv en hideactivitytypes
```

## Support
![screenshot](/images/joinery-logo.png)

Joinery provides services for CiviCRM including custom extension development, training, data migrations, and more. We aim to keep this extension in good working order, and will do our best to respond appropriately to issues reported on its [github issue queue](https://github.com/twomice/com.joineryhq.hideactivitytypes/issues). In addition, if you require urgent or highly customized improvements to this extension, we may suggest conducting a fee-based project under our standard commercial terms.  In any case, the place to start is the [github issue queue](https://github.com/twomice/com.joineryhq.hideactivitytypes/issues) -- let us hear what you need and we'll be glad to help however we can.

And, if you need help with any other aspect of CiviCRM -- from hosting to custom development to strategic consultation and more -- please contact us directly via https://joineryhq.com
