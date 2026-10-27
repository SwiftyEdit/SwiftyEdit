# Security Policy

## Reporting a vulnerability

Please report security issues **privately by e-mail to support@swiftyedit.com** - not in the
public issue tracker, forum or social media, not even for issues that look minor. Many
SwiftyEdit sites can't update immediately, so details should only become public once a fixed
version is available.

Helpful to include:

- the affected version and build (see `version.json` or the update page in the backend),
- what an attacker can do and which access they need (none, frontend account, backend user ...),
- steps to reproduce, ideally with a short proof of concept,
- your name or handle, if you would like to be credited.

Please don't access, change or delete data that isn't yours, and only test against your own
installation.

## What happens next

1. **Within 7 days** we confirm that we received your report.
2. We verify the issue and tell you our assessment - whether we can reproduce it, how severe we
   consider it and roughly when a fix can be expected.
3. We develop the fix and keep you informed. If you like, you can check it before the release.
4. The fix is released in a new version. The changelog names the fixed issue, and for serious
   issues we publish a security advisory (and request a CVE if appropriate).
5. After the release, you are free to publish your findings. If you need more time or want to
   publish earlier, please talk to us - we prefer to agree on a date together.

We credit reporters in the changelog and the advisory, unless you prefer to stay anonymous.

## Supported versions

Security fixes are made for the **latest release of SwiftyEdit 2** only. Please update to the
latest version before reporting, and check whether the issue still exists there.

| Version | Supported |
| --- | --- |
| 2.x (latest release) | yes |
| older 2.x releases | no - please update |
| 1.x | no |

## Scope

In scope: the SwiftyEdit core and the plugins and themes that ship with it (payment plugins,
editor plugins, default theme).

Not in scope:

- third-party or self-written plugins and themes - please contact their authors,
- issues that require a modified core, an insecure server configuration or access to the
  server or the database,
- missing hardening without a concrete attack (e.g. a missing header), unless it leads to an
  exploitable issue - suggestions are still welcome as a normal issue.
