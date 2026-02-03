#!/usr/bin/env perl
use strict;
use warnings;
use POSIX qw(strftime);
use URI::Escape qw(uri_unescape);

my $method = $ENV{'REQUEST_METHOD'} || "GET";

my $host = $ENV{'HTTP_HOST'} || "";
my $ua   = $ENV{'HTTP_USER_AGENT'} || "";
my $ip   = $ENV{'HTTP_X_FORWARDED_FOR'} || $ENV{'HTTP_X_REAL_IP'} || $ENV{'REMOTE_ADDR'} || "unknown";
$ip =~ s/\s.*$//;

my $content_type = $ENV{'CONTENT_TYPE'} || "";
my $clen = $ENV{'CONTENT_LENGTH'} || 0;

my $now = strftime("%Y-%m-%dT%H:%M:%SZ", gmtime());

my $raw_query = $ENV{'QUERY_STRING'} || "";

my $raw_body = "";
if ($clen && $clen =~ /^\d+$/ && $clen > 0) {
  read(STDIN, $raw_body, $clen);
}

sub hescape {
  my ($s) = @_;
  $s //= "";
  $s =~ s/&/&amp;/g;
  $s =~ s/</&lt;/g;
  $s =~ s/>/&gt;/g;
  $s =~ s/"/&quot;/g;
  return $s;
}

sub parse_kv {
  my ($s) = @_;
  my %h;
  for my $pair (split /&/, ($s // "")) {
    next if $pair eq "";
    my ($k, $v) = split(/=/, $pair, 2);
    $k = uri_unescape($k // "");
    $v = uri_unescape($v // "");
    push @{ $h{$k} }, $v;
  }
  return \%h;
}

sub parse_json_flat {
  my ($s) = @_;
  my %h;
  return \%h if !defined $s;
  my $t = $s;
  $t =~ s/^\s+|\s+$//g;
  return \%h if $t !~ /^\{.*\}$/s;

  while ($t =~ /"([^"\\]*(?:\\.[^"\\]*)*)"\s*:\s*(?:"([^"\\]*(?:\\.[^"\\]*)*)"|(-?\d+(?:\.\d+)?|true|false|null))/g) {
    my $k = $1;
    my $v = defined $2 ? $2 : $3;
    $k =~ s/\\"/"/g; $k =~ s/\\\\/\\/g;
    if (defined $2) { $v =~ s/\\"/"/g; $v =~ s/\\\\/\\/g; }
    $h{$k} = $v;
  }
  return \%h;
}

my $parsed;
if ($method eq "GET") {
  $parsed = parse_kv($raw_query);
} elsif ($content_type =~ m{application/x-www-form-urlencoded}i) {
  $parsed = parse_kv($raw_body);
} elsif ($content_type =~ m{application/json}i) {
  $parsed = parse_json_flat($raw_body);
} else {
  $parsed = {
    %{ parse_kv($raw_query) },
    %{ parse_kv($raw_body) },
  };
}

sub dump_parsed {
  my ($p) = @_;
  my $out = "{\n";
  if (ref($p) eq "HASH") {
    my @keys = sort keys %$p;
    for (my $i=0; $i<@keys; $i++) {
      my $k = $keys[$i];
      my $v = $p->{$k};
      my $comma = ($i == @keys-1) ? "" : ",";
      if (ref($v) eq "ARRAY") {
        my @arr = map { my $x=$_; $x =~ s/\\/\\\\/g; $x =~ s/"/\\"/g; "\"$x\"" } @$v;
        $out .= qq(  "$k": [ ) . join(", ", @arr) . qq( ]$comma\n);
      } else {
        my $x = defined $v ? $v : "";
        $x =~ s/\\/\\\\/g;
        $x =~ s/"/\\"/g;
        $out .= qq(  "$k": "$x"$comma\n);
      }
    }
  }
  $out .= "}\n";
  return $out;
}

print "Content-Type: text/html; charset=utf-8\r\n\r\n";
print <<"HTML";
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Echo (Perl)</title>
</head>
<body>
  <h1>Echo (Perl)</h1>

  <h2>Request Info</h2>
  <ul>
    <li><b>Host:</b> @{[hescape($host)]}</li>
    <li><b>Time (UTC):</b> @{[hescape($now)]}</li>
    <li><b>Client IP:</b> @{[hescape($ip)]}</li>
    <li><b>User-Agent:</b> @{[hescape($ua)]}</li>
    <li><b>Method:</b> @{[hescape($method)]}</li>
    <li><b>Content-Type:</b> @{[hescape($content_type)]}</li>
  </ul>

  <h2>Raw Data</h2>
  <h3>Query String</h3>
  <pre>@{[hescape($raw_query)]}</pre>

  <h3>Body</h3>
  <pre>@{[hescape($raw_body)]}</pre>

  <h2>Parsed Data</h2>
  <pre>@{[hescape(dump_parsed($parsed))]}</pre>
</body>
</html>
HTML
