# Union Platform Backend

> Drupal companion for [UnionPlatform][0].

Union Platform is an application to improve the communication between labor
unions and its members. It allows users to share news and documents, enroll
through the app and upload complaints and georeferenced alerts using a drupal
powered backend. It was developed with the support of [FES Argentina][0].

This is a set of modules to set up a drupal backend for the application.

## Installation

You need to add this repository to your `composer.json`

```json
{
  "type": "package",
  "package": {
    "name": "drupal/rest_password_reset",
    "version": "0.0.1",
    "type": "drupal-module",
    "source": {
      "url": "https://git.drupalcode.org/sandbox/mpv-3154004.git",
      "type": "git",
      "reference": "8.x-1.x"
    }
  }
}
```

[0]: https://gitlab.com/gcoop-libre/unionplatform
[1]: https://www.fes-argentina.org
