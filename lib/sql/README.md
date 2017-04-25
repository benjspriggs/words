# SQL Scripts
## Scripts
- ``setup.sql``
 => Sets up all of the following tables.
- ``testinit.sql``
 => Initializes an existing database with test data.
- ``prodinit.sql``
 => Initializes an existing database with production data - two users, admin and guest, neither of which have passwords set.

## Tables
General table layout:

- poem\_content
- poem\_meta
- user

### ``poem_content``
- poem\_id
- body

### ``poem_meta``
- poem\_id (fk)
- title
- tags
- post date
- written\_date

### ``user``
- username
- userid
- pass\_hash
- user\_salt

### ``user_meta``
- userid (fk)
- has\_admin

### ``user_sessions``
- userid (fk)
- session\_hash
- last\_login
- logged\_in

### ``poem_view``
- poem\_id (fk)
- view\_count
