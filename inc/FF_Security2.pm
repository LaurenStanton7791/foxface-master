package CG_Security2;
$VERSION = 0.10;

use DBI;
use Crypt::OpenSSL::RSA;
use MIME::Base64;
use Convert::PEM;
use File::Slurp;
use Date::Simple ('date', 'today');

use CGI::Cookie;

use Crypt::RSA;
use Crypt::RSA::Key::Public;
use Crypt::RSA::Key::Private;
use Crypt::JWT ':all';

our $dbh = DBI->connect('DBI:mysql:CohortGroups:127.0.0.1:3306;mysql_socket=/var/lib/mysql/mysql.sock', 'ronv', 'mi9nk', {RaiseError => 1, AutoCommit => 1});

sub cg_getTokenUser {
	my $SECRET_KEY = $_[0];
	
	# fetch existing cookies
	my %cookies = fetch CGI::Cookie;
	
	my $token;
	eval {
		$token = $cookies{'access_token'}->value;
	} or do {
		return 0;
	};	
	
	my $data = decode_jwt(token => $token, key => $SECRET_KEY);
	
	return $data->{'sub'};
}

sub cg_getTokenAcct {
	my $SECRET_KEY = $_[0];
	
	# fetch existing cookies
	my %cookies = fetch CGI::Cookie;
	
	my $token;
	eval {
		$token = $cookies{'access_token'}->value;
	} or do {
		return 0;
	};	
	
	my $data = decode_jwt(token => $token, key => $SECRET_KEY);
	
	return $data->{'client'};	
}

sub cg_getTokenUserAcct {
	my $SECRET_KEY = $_[0];
	
	# fetch existing cookies
	my %cookies = fetch CGI::Cookie;
	
	my $token;
	eval {
		$token = $cookies{'access_token'}->value;
	} or do {
		return 0;
	};	
	
	my $data = decode_jwt(token => $token, key => $SECRET_KEY);
	
	return ($data->{'sub'},$data->{'client'});	
}

sub cg_getTokenAdmin {
	my $SECRET_KEY = $_[0];
	
	# fetch existing cookies
	my %cookies = fetch CGI::Cookie;
	
	my $token;
	eval {
		$token = $cookies{'access_token'}->value;
	} or do {
		return 0;
	};	
	
	my $data = decode_jwt(token => $token, key => $SECRET_KEY);
	
	return $data->{'is_admin'}	
}

sub cg_getSecretKey {
	my @lines = read_file( '/var/www/html2/inc/.env' ) ;
	
	foreach my $line (@lines) {
		my ($key,$value) = split('=',$line);
		if ($key eq 'SECRET_KEY') {
			$value =~ s/^\s+//; #Trim leading white space
			$value =~ s/\s+$//; #Trim trailing whitespace

			return $value;
		}		
	}
	return '';
}

1;