# Introduction

This library contains a class dedicated to parsing [tibia.com][tibiacom], the official site of the MMORPG
Tibia.

They have no API, so if you need any information to be gathered automatically, you need to parse
their html output. This library is the first step towards creating a new, minimal fansite, based on
[Silex][silex].

  [tibiacom]: http://tibia.com/
  [silex]: http://silex.sensiolabs.org/

# Installation

Install the package `maerlyn/tibiacom-parser` via [composer].

  [composer]: http://getcomposer.org

# Dependencies

The only dependency is [Guzzle].

  [Guzzle]: http://packagist.org/packages/guzzle/guzzle

# Usage

## Getting information about a character

    $tibiadotcom->characterInfo($name);

**Parameters**:

 - `$name`: the name of the character

**Return value**:

an array with the following keys, but only if they exist in the characterinfo table on the site. All
values not stated otherwise are strings.

 - name
 - sex
 - vocation
 - level
 - achievement_points
 - world
 - residence
 - house
 - guild (an array with keys `name` and `rank`)
 - last_login (a `\DateTime` object)
 - account_status

**Exceptions**:

throws `Maerlyn\Tibia\CharacterNotFoundException` if the character does not exist.

## Getting the list of online characters on a server

    $tibiadotcom->whoIsOnline($world)

**Parameters**:

 - `$world`: the name of the server

**Return value**:

a numeric array with all characters, where each item is an array with the following keys:

 - name
 - level
 - vocation

## Getting the character's deaths

    $tibiadotcom->characterDeaths($name)

**Parameters**:

 - `$name`: the name of the character

** Return value**:

an array of arrays, each inner array is a death with the following keys:

 - date (a `\DateTime` instance)
 - level
 - reason

**Exceptions**:

throws `Maerlyn\Tibia\CharacterNotFoundException` if the character does not exist.

# License

The code is released under the MIT license.
