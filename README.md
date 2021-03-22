BIND9 to Cloudflare
===

PHP script wrapping curl calls to the v4 API of Cloudflare to import a list of domain with their records (BIND zone file).

- Populate `job/domains.txt` with one domain per line
- Copy/Paste the zone files into `jobs/` (the script awaits that zones file names are in the shape `[domain].host`)
- `./import.php`
