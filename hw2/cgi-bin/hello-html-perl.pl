#!/usr/bin/env perl
use strict;
use warnings;
use POSIX qw(strftime);

my $now = strftime("%Y-%m-%dT%H:%M:%SZ", gmtime());
my $ip  = $ENV{'HTTP_X_FORWARDED_FOR'} || $ENV{'HTTP_X_REAL_IP'} || $ENV{'REMOTE_ADDR'} || "unknown";
$ip =~ s/\s.*$//;

print "Content-Type: text/html; charset=utf-8\r\n\r\n";
print <<"HTML";
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Hello HTML (Perl)</title>
</head>
<body>
  <h1>Hello World from Austin (Perl)!</h1>
  <p><b>Language:</b> Perl</p>
  <p><b>Time (UTC):</b> $now</p>
  <p><b>Your IP:</b> $ip</p>
</body>
</html>
HTML
