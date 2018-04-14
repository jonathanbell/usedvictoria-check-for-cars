# UsedVictoria.com Check for Cars PHP Script

Screen scraping PHP script that you can put on a `cron` task in order to check for new used vehicles on usedvictoria.com

## Installation

1.  Download or clone this repository to a directory on your server/computer.
1.  `cd` into the root of the cloned repository.
1.  `composer install`
1.  `touch last_car.log && touch notifications.log`
1.  `cp config.ini.example config.ini`
1.  Edit all of the values inside `config.ini` to match those of your SMTP server and your search parameters. Use [an email address that will text your smartphone](https://www.rogers.com/customer/support/article/set-up-email-to-text) if you'd like SMS notifications.

Then setup `cron` on your server to run the `checkforcars.php` script on an interval (something like every hour would work well).

```bash
/path/to/php /path/to/usedvictoria-check-for-cars/checkforcars.php
```
