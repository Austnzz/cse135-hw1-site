#!/usr/bin/env perl
use strict;
use warnings;
use POSIX qw(strftime);

my $now = strftime("%Y-%m-%dT%H:%M:%SZ", gmtime());
my $ip  = $ENV{'HTTP_X_FORWARDED_FOR'} || $ENV{'HTTP_X_REAL_IP'} || $ENV{'REMOTE_ADDR'} || "unknown";
$ip =~ s/\s.*$//;

sub jescape {
  my ($s) = @_;
  $s =~ s/\\/\\\\/g;
  $s =~ s/"/\\"/g;
  $s =~ s/\r/\\r/g;
  $s =~ s/\n/\\n/g;
  $s =~ s/\t/\\t/g;
  return $s;
}

print "Content-Type: application/json; charset=utf-8\r\n\r\n";
print "{\n";
print '  "title": "Hello, Perl!",' . "\n";
print '  "heading": "Hello, Perl!",' . "\n";
print '  "message": "Hello World from Austin (Perl)!",' . "\n";
print '  "time": "' . jescape($now) . '",' . "\n";
print '  "ipAddress": "' . jescape($ip) . "\"\n";
print "}\n";
