# ARDC-customized abraham/twitteroauth module

We have the problem of needing to use Twitter API v2 in a PHP 5
codebase.

To solve that problem, this directory contains a modified copy of
release 3.0.0 of the abraham/twitteroauth module. The code has been
run through a PHP 7-to-PHP 5 transpiler. The result has then been
edited by hand to restore some things the transpiler deleted: the
licence headers in each file, and many blank lines.

Finally, the use of the Composer\CaBundle\CaBundle package has been
commented out.
