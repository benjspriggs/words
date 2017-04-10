# words
Minimal poetry website.

# Configuration

All that's required is a repo and a [Webhook for that repo](https://developer.github.com/webhooks/). Create a new file, `app.config.yml`, with all required fields (described below):

```yaml
---
deploy:
  key: value
```
etc.

## Required Values

| key name            | type   | description                                                              |
|---------------------+--------+--------------------------------------------------------------------------|
| secret              | string | Secret key.                                                              |
| remote_url          | string | Github repo https URL.                                                   |
| branch              | string | Branch in the Github repo to be cloned.                                  |
| path                | string | The full path to the directory on the server that is being deployed to.  |
| make_clean          | bool   | Whether or not to delete files not present in the repo before a staging. |
| cleanup_after_stage | bool   | Whether or not to delete the staging directory after each staging.       |
| timeout             | int    | Timeout, in seconds, of each command.                                    |

## Optional Values

All values will default to `false` if not provided:

| key name      | type   | description                                               |
|---------------+--------+-----------------------------------------------------------|
| backup        | string | Location to where files will be copied to before staging. |
| composer      | bool   | Whether or not to use Composer.                           |
| composer_home | string | Location of Composer on the destination server.           |
| email         | string | Email that will be notified on deployment error.          |

