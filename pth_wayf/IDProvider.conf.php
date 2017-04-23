<?php // Copyright (c) 2013, SWITCH - Serving Swiss Universities

// WAYF Identity Provider Configuration file

// Find below some example entries of Identity Providers, categories and 
// cascaded WAYFs
// The keys of $IDProviders must correspond to the entityId of the 
// Identity Providers or a unique value in case of a cascaded WAYF/DS or 
// a category. In the case of a category, the key must correspond to the the 
// Type value of Identity Provider entries.
// The sequence of IdPs and SPs play a role. No sorting is done.
// 
// Please read the file DOC for information on the format of the entries

// Category

$mysqli = new mysqli("localhost", "dtsearch", "dtsearch", "cairn3_pub");
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}


$IDProviders['university'] = array (
		'Type' => 'category',
		'Name' => 'France',
);

$x=0;
$req="select title,URL_LOGIN from SSOCAS";

$result = $mysqli->query($req);
while ($row = mysqli_fetch_assoc($result) ) {
$login=$row["URL_LOGIN"];
//$login="https://test.federation.renater.fr/idp/profile/SAML2/POST/SSO";
$title=$row["title"];
$x++;
$login=  substr($login,0,strpos($login,"?shire=https://www"));
//$login="https://test.federation.renater.fr/idp/profile/SAML2/POST/SSO";
//https://ident.groupe-esa.com/idp/profile/Shibboleth/SSO?shire=https://www.cairn.info/shibboleth/Shibboleth.sso/SAML/POST&target=https://www.cairn.info/shibboleth/ident_sso2.php&providerId=https://www.cairn.info/

$IDProviders[$login] = array(
        'SSO' => $login,
        'Name' => $title,
        'Type' => 'university',
);

}

$IDProviders['https://test.federation.renater.fr/idp/shibboleth'] = array(
        'SSO' => 'https://test.federation.renater.fr/idp/profile/SAML2/POST/SSO',
        'Name' => 'RENATER TEST',
        'Type' => 'university',
);

/*
$IDProviders['https://idp.epfl.ch/idp/shibboleth'] = array(
        'SSO' => 'https://idp.epfl.ch/idp/profile/Shibboleth/SSO',
        'Name' => 'EPF Lausanne',
        'Type' => 'university',
);

$IDProviders['https://aai-logon.ethz.ch/idp/shibboleth'] = array(
        'SSO' => 'https://aai-logon.ethz.ch/idp/profile/Shibboleth/SSO',
        'Name' => 'ETH Z&uuml;rich',
        'de' => array ('Name' => 'ETH Z&uuml;rich'),
        'en' => array ('Name' => 'ETH Zurich'),
        'Type' => 'university',
	'IP' => array ('193.166.2.0/24','129.132.0.0/16'),
);

$IDProviders['https://aai-idp.uzh.ch/idp/shibboleth'] = array(
        'SSO' => 'https://aai-idp.uzh.ch/idp/profile/Shibboleth/SSO',
        'Name' => 'Universit&auml;t Z&uuml;rich',
        'Type' => 'university',
	'Realm' => 'uzh.ch',
        'en' => array ('Keywords' => 'Zurich Irchel+Park'),
);


// Category
$IDProviders['vho'] = array (
		'Type' => 'category',
		'Name' => 'Virtual Home Organizations',
);

// An example of a configuration with multiple network blocks and multiple languages 
$IDProviders['https://aai-logon.vho-switchaai.ch/idp/shibboleth'] = array (
		'Type' => 'vho',
		'Name' => 'Virtual Home Organisation',
		'en' => array (
			'Name' => 'Virtual Home Organisation',
			'Keywords','Zurich Switzerland',
			),
		'de' => array (
			'Name' => 'Virtuelle Home Organisation',
			'Keywords','Z�rich Schweiz',
			),
		'fr' => array ('Name' => 'Home Organisation Virtuelle'),
		'it' => array ('Name' => 'Virtuale Home Organisation'),
		'IP' => array ('130.59.6.0/16','127.0.0.0/24'),
		'SSO' => 'https://aai-logon.vho-switchaai.ch/idp/profile/Shibboleth/SSO',
);

// Example of an IDP you want not to be displayed when IDPs are parsed from
// a metadata file and SAML2MetaOverLocalConf is set to false
//$IDProviders['urn:mace:switch.ch:SWITCHaai:invisibleidp'] = '-';


// Category
$IDProviders['unknown'] = array (
		'Type' => 'category',
		'Name' => 'Others',
		'de' => array ('Name' => 'Andere'),
		'fr' => array ('Name' => 'Autres'),
		'it' => array ('Name' => 'Altri'),
);
*/
?>
