<?php

	if(IsSet($_NAMESERVERS)
	&& (GetType($_NAMESERVERS)!="array"
	|| count($_NAMESERVERS)==0))
		Unset($_NAMESERVERS);

	require( 'DNS.php' );
	define( 'RESOLV_CONF_PATH', '/etc/resolv.conf' );

	if( !isset( $_NAMESERVERS ) ) {
		$_NAMESERVERS = array();
		if( strncmp( PHP_OS, "WIN", 3 ) == 0 ) {
			unset( $res );
			exec( 'ipconfig /all', $res );
			$cnt = count( $res );
			for( $i = 0; $i < $cnt; ++$i ) {
				if( strpos( $res[$i], 'DNS Servers' ) !== false ) {
					$_NAMESERVERS[] = substr( $res[$i], strpos( $res[$i], ': ' ) + 2 );
					break;
				}
			}
			while( $i<$cnt-1 && strpos( $res[++$i], ':' ) === false ) {
				$_NAMESERVERS[] = trim( $res[$i] );
			}
		} elseif( file_exists( RESOLV_CONF_PATH ) ) {
			$lines = file( RESOLV_CONF_PATH );
			$cnt = count( $lines );
			for( $i = 0; $i < $cnt; ++$i ) {
				list( $dr, $val ) = split( '[ \t]', $lines[$i] );
				if( $dr == 'nameserver' ) {
					$_NAMESERVERS[] = rtrim( $val );
				}
			}
			unset( $lines );
		}
	}

	if(count($_NAMESERVERS))
		$__PHPRESOLVER_RS = new DNSResolver( $_NAMESERVERS[0] );
	else
	{
		Unset($_NAMESERVERS);
		Unset($__PHPRESOLVER_RS);
	}

	function GetMXRR( $hostname, &$mxhosts, &$weight ) {
		global $__PHPRESOLVER_RS;
		if(!IsSet($__PHPRESOLVER_RS))
			return(false);
		$dnsname = & DNSName::newFromString( $hostname );
		$answer = & $__PHPRESOLVER_RS->sendQuery(
		  new DNSQuery(
		    new DNSRecord( $dnsname, DNS_RECORDTYPE_MX )
		  )
		);
		if( $answer === false || $answer->rec_answer === false ) {
			return false;
		} else {
			$i = count( $answer->rec_answer );
			$mxhosts = $weight = array();
			while( --$i >= 0 ) {
				if( $answer->rec_answer[$i]->type == DNS_RECORDTYPE_MX ) {
					$rec = &$answer->rec_answer[$i]->specific_fields;
					$mxhosts[] = substr( $rec['exchange']->getCanonicalName(), 0, -1 );
					$weight[] = $rec['preference'];
				}
			}
			return true;
		}
	}

?>