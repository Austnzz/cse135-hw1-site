#!/usr/bin/env perl
use strict;
use warnings;

sub hescape {
  my ($s) = @_;
  $s =~ s/&/&amp;/g;
  $s =~ s/</&lt;/g;
  $s =~ s/>/&gt;/g;
  $s =~ s/"/&quot;/g;
  return $s;
}

print "Content-Type: text/html; charset=utf-8\r\n\r\n";
print <<"HTML";
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Perl Environment Variables</title>
</head>
<body>
  <h1>Perl Environment Variables</h1>
  <hr>
  <ul>
HTML

for my $k (sort keys %ENV) {
  my $v = defined $ENV{$k} ? $ENV{$k} : "";
  print "    <li><b>" . hescape($k) . "</b> = " . hescape($v) . "</li>\n";
}

print <<"HTML";
  </ul>
</body>
</html>
HTML
