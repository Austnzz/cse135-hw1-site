#!/usr/bin/env perl
use strict;
use warnings;
use POSIX qw(strftime);
use URI::Escape qw(uri_unescape);

my $cookie_name = "hw2_state_perl";
my $method = $ENV{'REQUEST_METHOD'} || "GET";
my $raw_query = $ENV{'QUERY_STRING'} || "";
my $now = strftime("%Y-%m-%dT%H:%M:%SZ", gmtime());
my $host = $ENV{'HTTP_HOST'} || "";
my $ip   = $ENV{'HTTP_X_FORWARDED_FOR'} || $ENV{'HTTP_X_REAL_IP'} || $ENV{'REMOTE_ADDR'} || "unknown";
$ip =~ s/\s.*$//;

sub hescape {
  my ($s) = @_;
  $s //= "";
  $s =~ s/&/&amp;/g;
  $s =~ s/</&lt;/g;
  $s =~ s/>/&gt;/g;
  $s =~ s/"/&quot;/g;
  return $s;
}

sub parse_qs {
  my ($s) = @_;
  my %h;
  for my $pair (split /&/, ($s // "")) {
    next if $pair eq "";
    my ($k, $v) = split(/=/, $pair, 2);
    $k = uri_unescape($k // "");
    $v = uri_unescape($v // "");
    $h{$k} = $v;
  }
  return \%h;
}

sub get_cookie {
  my ($name) = @_;
  my $raw = $ENV{'HTTP_COOKIE'} || "";
  for my $c (split /;\s*/, $raw) {
    my ($k, $v) = split(/=/, $c, 2);
    next if !defined $k;
    if ($k eq $name) {
      return uri_unescape($v // "");
    }
  }
  return "";
}

my $q = parse_qs($raw_query);
my $action = $q->{action} // "";
my $value  = $q->{value}  // "";

my @headers;
push @headers, "Content-Type: text/html; charset=utf-8";

if ($action eq "set") {
  # keep cookie value simple (no quotes) to avoid header issues
  $value =~ s/[\r\n]//g;
  push @headers, "Set-Cookie: $cookie_name=$value; Max-Age=86400; Path=/; Secure; SameSite=Lax";
} elsif ($action eq "clear") {
  push @headers, "Set-Cookie: $cookie_name=; Max-Age=0; Path=/; Secure; SameSite=Lax";
}

print join("\r\n", @headers) . "\r\n\r\n";

my $saved = get_cookie($cookie_name);

print <<"HTML";
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>State (Perl)</title>
</head>
<body>
  <h1>State Demo (Perl)</h1>

  <ul>
    <li><b>Host:</b> @{[hescape($host)]}</li>
    <li><b>Time (UTC):</b> @{[hescape($now)]}</li>
    <li><b>Your IP:</b> @{[hescape($ip)]}</li>
    <li><b>Method:</b> @{[hescape($method)]}</li>
    <li><b>Cookie name:</b> <code>@{[hescape($cookie_name)]}</code></li>
  </ul>

  <p><b>Saved value:</b> <code>@{[hescape($saved)]}</code></p>

  <p>
    <a href="/hw2/cgi-bin/state-perl.pl">Refresh</a>
    <a href="/hw2/cgi-bin/state-perl.pl?action=clear">Clear</a>
  </p>

  <form method="GET" action="/hw2/cgi-bin/state-perl.pl">
    <input type="hidden" name="action" value="set" />
    <label>Set value:
      <input type="text" name="value" placeholder="e.g. test1" />
    </label>
    <button type="submit">Save</button>
  </form>
</body>
</html>
HTML
