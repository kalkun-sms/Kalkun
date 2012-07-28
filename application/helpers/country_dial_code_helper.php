<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Country Dial Code
* <http://ninetwozero.com/2011/12/04/php-country-array-with-dial-codes/>
**/

function getCountryInformation($filter = '') {
	
	//Let's define the array
	$countryArray = array(
	    'AD' => array(
	        'country_name' => 'ANDORRA',
	        'dial_code' => '376'
	    ),
	    'AE' => array(
	        'country_name' => 'UNITED ARAB EMIRATES',
	        'dial_code' => '971'
	    ),
	    'AF' => array(
	        'country_name' => 'AFGHANISTAN',
	        'dial_code' => '93'
	    ),
	    'AG' => array(
	        'country_name' => 'ANTIGUA AND BARBUDA',
	        'dial_code' => '1268'
	    ),
	    'AI' => array(
	        'country_name' => 'ANGUILLA',
	        'dial_code' => '1264'
	    ),
	    'AL' => array(
	        'country_name' => 'ALBANIA',
	        'dial_code' => '355'
	    ),
	    'AM' => array(
	        'country_name' => 'ARMENIA',
	        'dial_code' => '374'
	    ),
	    'AN' => array(
	        'country_name' => 'NETHERLANDS ANTILLES',
	        'dial_code' => '599'
	    ),
	    'AO' => array(
	        'country_name' => 'ANGOLA',
	        'dial_code' => '244'
	    ),
	    'AQ' => array(
	        'country_name' => 'ANTARCTICA',
	        'dial_code' => '672'
	    ),
	    'AR' => array(
	        'country_name' => 'ARGENTINA',
	        'dial_code' => '54'
	    ),
	    'AS' => array(
	        'country_name' => 'AMERICAN SAMOA',
	        'dial_code' => '1684'
	    ),
	    'AT' => array(
	        'country_name' => 'AUSTRIA',
	        'dial_code' => '43'
	    ),
	    'AU' => array(
	        'country_name' => 'AUSTRALIA',
	        'dial_code' => '61'
	    ),
	    'AW' => array(
	        'country_name' => 'ARUBA',
	        'dial_code' => '297'
	    ),
	    'AZ' => array(
	        'country_name' => 'AZERBAIJAN',
	        'dial_code' => '994'
	    ),
	    'BA' => array(
	        'country_name' => 'BOSNIA AND HERZEGOVINA',
	        'dial_code' => '387'
	    ),
	    'BB' => array(
	        'country_name' => 'BARBADOS',
	        'dial_code' => '1246'
	    ),
	    'BD' => array(
	        'country_name' => 'BANGLADESH',
	        'dial_code' => '880'
	    ),
	    'BE' => array(
	        'country_name' => 'BELGIUM',
	        'dial_code' => '32'
	    ),
	    'BF' => array(
	        'country_name' => 'BURKINA FASO',
	        'dial_code' => '226'
	    ),
	    'BG' => array(
	        'country_name' => 'BULGARIA',
	        'dial_code' => '359'
	    ),
	    'BH' => array(
	        'country_name' => 'BAHRAIN',
	        'dial_code' => '973'
	    ),
	    'BI' => array(
	        'country_name' => 'BURUNDI',
	        'dial_code' => '257'
	    ),
	    'BJ' => array(
	        'country_name' => 'BENIN',
	        'dial_code' => '229'
	    ),
	    'BL' => array(
	        'country_name' => 'SAINT BARTHELEMY',
	        'dial_code' => '590'
	    ),
	    'BM' => array(
	        'country_name' => 'BERMUDA',
	        'dial_code' => '1441'
	    ),
	    'BN' => array(
	        'country_name' => 'BRUNEI DARUSSALAM',
	        'dial_code' => '673'
	    ),
	    'BO' => array(
	        'country_name' => 'BOLIVIA',
	        'dial_code' => '591'
	    ),
	    'BR' => array(
	        'country_name' => 'BRAZIL',
	        'dial_code' => '55'
	    ),
	    'BS' => array(
	        'country_name' => 'BAHAMAS',
	        'dial_code' => '1242'
	    ),
	    'BT' => array(
	        'country_name' => 'BHUTAN',
	        'dial_code' => '975'
	    ),
	    'BW' => array(
	        'country_name' => 'BOTSWANA',
	        'dial_code' => '267'
	    ),
	    'BY' => array(
	        'country_name' => 'BELARUS',
	        'dial_code' => '375'
	    ),
	    'BZ' => array(
	        'country_name' => 'BELIZE',
	        'dial_code' => '501'
	    ),
	    'CA' => array(
	        'country_name' => 'CANADA',
	        'dial_code' => '1'
	    ),
	    'CC' => array(
	        'country_name' => 'COCOS (KEELING) ISLANDS',
	        'dial_code' => '61'
	    ),
	    'CD' => array(
	        'country_name' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
	        'dial_code' => '243'
	    ),
	    'CF' => array(
	        'country_name' => 'CENTRAL AFRICAN REPUBLIC',
	        'dial_code' => '236'
	    ),
	    'CG' => array(
	        'country_name' => 'CONGO',
	        'dial_code' => '242'
	    ),
	    'CH' => array(
	        'country_name' => 'SWITZERLAND',
	        'dial_code' => '41'
	    ),
	    'CI' => array(
	        'country_name' => 'COTE D IVOIRE',
	        'dial_code' => '225'
	    ),
	    'CK' => array(
	        'country_name' => 'COOK ISLANDS',
	        'dial_code' => '682'
	    ),
	    'CL' => array(
	        'country_name' => 'CHILE',
	        'dial_code' => '56'
	    ),
	    'CM' => array(
	        'country_name' => 'CAMEROON',
	        'dial_code' => '237'
	    ),
	    'CN' => array(
	        'country_name' => 'CHINA',
	        'dial_code' => '86'
	    ),
	    'CO' => array(
	        'country_name' => 'COLOMBIA',
	        'dial_code' => '57'
	    ),
	    'CR' => array(
	        'country_name' => 'COSTA RICA',
	        'dial_code' => '506'
	    ),
	    'CU' => array(
	        'country_name' => 'CUBA',
	        'dial_code' => '53'
	    ),
	    'CV' => array(
	        'country_name' => 'CAPE VERDE',
	        'dial_code' => '238'
	    ),
	    'CX' => array(
	        'country_name' => 'CHRISTMAS ISLAND',
	        'dial_code' => '61'
	    ),
	    'CY' => array(
	        'country_name' => 'CYPRUS',
	        'dial_code' => '357'
	    ),
	    'CZ' => array(
	        'country_name' => 'CZECH REPUBLIC',
	        'dial_code' => '420'
	    ),
	    'DE' => array(
	        'country_name' => 'GERMANY',
	        'dial_code' => '49'
	    ),
	    'DJ' => array(
	        'country_name' => 'DJIBOUTI',
	        'dial_code' => '253'
	    ),
	    'DK' => array(
	        'country_name' => 'DENMARK',
	        'dial_code' => '45'
	    ),
	    'DM' => array(
	        'country_name' => 'DOMINICA',
	        'dial_code' => '1767'
	    ),
	    'DO' => array(
	        'country_name' => 'DOMINICAN REPUBLIC',
	        'dial_code' => '1809'
	    ),
	    'DZ' => array(
	        'country_name' => 'ALGERIA',
	        'dial_code' => '213'
	    ),
	    'EC' => array(
	        'country_name' => 'ECUADOR',
	        'dial_code' => '593'
	    ),
	    'EE' => array(
	        'country_name' => 'ESTONIA',
	        'dial_code' => '372'
	    ),
	    'EG' => array(
	        'country_name' => 'EGYPT',
	        'dial_code' => '20'
	    ),
	    'ER' => array(
	        'country_name' => 'ERITREA',
	        'dial_code' => '291'
	    ),
	    'ES' => array(
	        'country_name' => 'SPAIN',
	        'dial_code' => '34'
	    ),
	    'ET' => array(
	        'country_name' => 'ETHIOPIA',
	        'dial_code' => '251'
	    ),
	    'FI' => array(
	        'country_name' => 'FINLAND',
	        'dial_code' => '358'
	    ),
	    'FJ' => array(
	        'country_name' => 'FIJI',
	        'dial_code' => '679'
	    ),
	    'FK' => array(
	        'country_name' => 'FALKLAND ISLANDS (MALVINAS)',
	        'dial_code' => '500'
	    ),
	    'FM' => array(
	        'country_name' => 'MICRONESIA, FEDERATED STATES OF',
	        'dial_code' => '691'
	    ),
	    'FO' => array(
	        'country_name' => 'FAROE ISLANDS',
	        'dial_code' => '298'
	    ),
	    'FR' => array(
	        'country_name' => 'FRANCE',
	        'dial_code' => '33'
	    ),
	    'GA' => array(
	        'country_name' => 'GABON',
	        'dial_code' => '241'
	    ),
	    'GB' => array(
	        'country_name' => 'UNITED KINGDOM',
	        'dial_code' => '44'
	    ),
	    'GD' => array(
	        'country_name' => 'GRENADA',
	        'dial_code' => '1473'
	    ),
	    'GE' => array(
	        'country_name' => 'GEORGIA',
	        'dial_code' => '995'
	    ),
	    'GH' => array(
	        'country_name' => 'GHANA',
	        'dial_code' => '233'
	    ),
	    'GI' => array(
	        'country_name' => 'GIBRALTAR',
	        'dial_code' => '350'
	    ),
	    'GL' => array(
	        'country_name' => 'GREENLAND',
	        'dial_code' => '299'
	    ),
	    'GM' => array(
	        'country_name' => 'GAMBIA',
	        'dial_code' => '220'
	    ),
	    'GN' => array(
	        'country_name' => 'GUINEA',
	        'dial_code' => '224'
	    ),
	    'GQ' => array(
	        'country_name' => 'EQUATORIAL GUINEA',
	        'dial_code' => '240'
	    ),
	    'GR' => array(
	        'country_name' => 'GREECE',
	        'dial_code' => '30'
	    ),
	    'GT' => array(
	        'country_name' => 'GUATEMALA',
	        'dial_code' => '502'
	    ),
	    'GU' => array(
	        'country_name' => 'GUAM',
	        'dial_code' => '1671'
	    ),
	    'GW' => array(
	        'country_name' => 'GUINEA-BISSAU',
	        'dial_code' => '245'
	    ),
	    'GY' => array(
	        'country_name' => 'GUYANA',
	        'dial_code' => '592'
	    ),
	    'HK' => array(
	        'country_name' => 'HONG KONG',
	        'dial_code' => '852'
	    ),
	    'HN' => array(
	        'country_name' => 'HONDURAS',
	        'dial_code' => '504'
	    ),
	    'HR' => array(
	        'country_name' => 'CROATIA',
	        'dial_code' => '385'
	    ),
	    'HT' => array(
	        'country_name' => 'HAITI',
	        'dial_code' => '509'
	    ),
	    'HU' => array(
	        'country_name' => 'HUNGARY',
	        'dial_code' => '36'
	    ),
	    'ID' => array(
	        'country_name' => 'INDONESIA',
	        'dial_code' => '62'
	    ),
	    'IE' => array(
	        'country_name' => 'IRELAND',
	        'dial_code' => '353'
	    ),
	    'IL' => array(
	        'country_name' => 'ISRAEL',
	        'dial_code' => '972'
	    ),
	    'IM' => array(
	        'country_name' => 'ISLAND OF MAN',
	        'dial_code' => '44'
	    ),
	    'IN' => array(
	        'country_name' => 'INDIA',
	        'dial_code' => '91'
	    ),
	    'IQ' => array(
	        'country_name' => 'IRAQ',
	        'dial_code' => '964'
	    ),
	    'IR' => array(
	        'country_name' => 'IRAN, ISLAMIC REPUBLIC OF',
	        'dial_code' => '98'
	    ),
	    'IS' => array(
	        'country_name' => 'ICELAND',
	        'dial_code' => '354'
	    ),
	    'IT' => array(
	        'country_name' => 'ITALY',
	        'dial_code' => '39'
	    ),
	    'JM' => array(
	        'country_name' => 'JAMAICA',
	        'dial_code' => '1876'
	    ),
	    'JO' => array(
	        'country_name' => 'JORDAN',
	        'dial_code' => '962'
	    ),
	    'JP' => array(
	        'country_name' => 'JAPAN',
	        'dial_code' => '81'
	    ),
	    'KE' => array(
	        'country_name' => 'KENYA',
	        'dial_code' => '254'
	    ),
	    'KG' => array(
	        'country_name' => 'KYRGYZSTAN',
	        'dial_code' => '996'
	    ),
	    'KH' => array(
	        'country_name' => 'CAMBODIA',
	        'dial_code' => '855'
	    ),
	    'KI' => array(
	        'country_name' => 'KIRIBATI',
	        'dial_code' => '686'
	    ),
	    'KM' => array(
	        'country_name' => 'COMOROS',
	        'dial_code' => '269'
	    ),
	    'KN' => array(
	        'country_name' => 'SAINT KITTS AND NEVIS',
	        'dial_code' => '1869'
	    ),
	    'KP' => array(
	        'country_name' => 'KOREA DEMOCRATIC PEOPLES REPUBLIC OF',
	        'dial_code' => '850'
	    ),
	    'KR' => array(
	        'country_name' => 'KOREA REPUBLIC OF',
	        'dial_code' => '82'
	    ),
	    'KW' => array(
	        'country_name' => 'KUWAIT',
	        'dial_code' => '965'
	    ),
	    'KY' => array(
	        'country_name' => 'CAYMAN ISLANDS',
	        'dial_code' => '1345'
	    ),
	    'KZ' => array(
	        'country_name' => 'KAZAKSTAN',
	        'dial_code' => '7'
	    ),
	    'LA' => array(
	        'country_name' => 'LAO PEOPLES DEMOCRATIC REPUBLIC',
	        'dial_code' => '856'
	    ),
	    'LB' => array(
	        'country_name' => 'LEBANON',
	        'dial_code' => '961'
	    ),
	    'LC' => array(
	        'country_name' => 'SAINT LUCIA',
	        'dial_code' => '1758'
	    ),
	    'LI' => array(
	        'country_name' => 'LIECHTENSTEIN',
	        'dial_code' => '423'
	    ),
	    'LK' => array(
	        'country_name' => 'SRI LANKA',
	        'dial_code' => '94'
	    ),
	    'LR' => array(
	        'country_name' => 'LIBERIA',
	        'dial_code' => '231'
	    ),
	    'LS' => array(
	        'country_name' => 'LESOTHO',
	        'dial_code' => '266'
	    ),
	    'LT' => array(
	        'country_name' => 'LITHUANIA',
	        'dial_code' => '370'
	    ),
	    'LU' => array(
	        'country_name' => 'LUXEMBOURG',
	        'dial_code' => '352'
	    ),
	    'LV' => array(
	        'country_name' => 'LATVIA',
	        'dial_code' => '371'
	    ),
	    'LY' => array(
	        'country_name' => 'LIBYAN ARAB JAMAHIRIYA',
	        'dial_code' => '218'
	    ),
	    'MA' => array(
	        'country_name' => 'MOROCCO',
	        'dial_code' => '212'
	    ),
	    'MC' => array(
	        'country_name' => 'MONACO',
	        'dial_code' => '377'
	    ),
	    'MD' => array(
	        'country_name' => 'MOLDOVA, REPUBLIC OF',
	        'dial_code' => '373'
	    ),
	    'ME' => array(
	        'country_name' => 'MONTENEGRO',
	        'dial_code' => '382'
	    ),
	    'MF' => array(
	        'country_name' => 'SAINT MARTIN',
	        'dial_code' => '1599'
	    ),
	    'MG' => array(
	        'country_name' => 'MADAGASCAR',
	        'dial_code' => '261'
	    ),
	    'MH' => array(
	        'country_name' => 'MARSHALL ISLANDS',
	        'dial_code' => '692'
	    ),
	    'MK' => array(
	        'country_name' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
	        'dial_code' => '389'
	    ),
	    'ML' => array(
	        'country_name' => 'MALI',
	        'dial_code' => '223'
	    ),
	    'MM' => array(
	        'country_name' => 'MYANMAR',
	        'dial_code' => '95'
	    ),
	    'MN' => array(
	        'country_name' => 'MONGOLIA',
	        'dial_code' => '976'
	    ),
	    'MO' => array(
	        'country_name' => 'MACAU',
	        'dial_code' => '853'
	    ),
	    'MP' => array(
	        'country_name' => 'NORTHERN MARIANA ISLANDS',
	        'dial_code' => '1670'
	    ),
	    'MR' => array(
	        'country_name' => 'MAURITANIA',
	        'dial_code' => '222'
	    ),
	    'MS' => array(
	        'country_name' => 'MONTSERRAT',
	        'dial_code' => '1664'
	    ),
	    'MT' => array(
	        'country_name' => 'MALTA',
	        'dial_code' => '356'
	    ),
	    'MU' => array(
	        'country_name' => 'MAURITIUS',
	        'dial_code' => '230'
	    ),
	    'MV' => array(
	        'country_name' => 'MALDIVES',
	        'dial_code' => '960'
	    ),
	    'MW' => array(
	        'country_name' => 'MALAWI',
	        'dial_code' => '265'
	    ),
	    'MX' => array(
	        'country_name' => 'MEXICO',
	        'dial_code' => '52'
	    ),
	    'MY' => array(
	        'country_name' => 'MALAYSIA',
	        'dial_code' => '60'
	    ),
	    'MZ' => array(
	        'country_name' => 'MOZAMBIQUE',
	        'dial_code' => '258'
	    ),
	    'NA' => array(
	        'country_name' => 'NAMIBIA',
	        'dial_code' => '264'
	    ),
	    'NC' => array(
	        'country_name' => 'NEW CALEDONIA',
	        'dial_code' => '687'
	    ),
	    'NE' => array(
	        'country_name' => 'NIGER',
	        'dial_code' => '227'
	    ),
	    'NG' => array(
	        'country_name' => 'NIGERIA',
	        'dial_code' => '234'
	    ),
	    'NI' => array(
	        'country_name' => 'NICARAGUA',
	        'dial_code' => '505'
	    ),
	    'NL' => array(
	        'country_name' => 'NETHERLANDS',
	        'dial_code' => '31'
	    ),
	    'NO' => array(
	        'country_name' => 'NORWAY',
	        'dial_code' => '47'
	    ),
	    'NP' => array(
	        'country_name' => 'NEPAL',
	        'dial_code' => '977'
	    ),
	    'NR' => array(
	        'country_name' => 'NAURU',
	        'dial_code' => '674'
	    ),
	    'NU' => array(
	        'country_name' => 'NIUE',
	        'dial_code' => '683'
	    ),
	    'NZ' => array(
	        'country_name' => 'NEW ZEALAND',
	        'dial_code' => '64'
	    ),
	    'OM' => array(
	        'country_name' => 'OMAN',
	        'dial_code' => '968'
	    ),
	    'PA' => array(
	        'country_name' => 'PANAMA',
	        'dial_code' => '507'
	    ),
	    'PE' => array(
	        'country_name' => 'PERU',
	        'dial_code' => '51'
	    ),
	    'PF' => array(
	        'country_name' => 'FRENCH POLYNESIA',
	        'dial_code' => '689'
	    ),
	    'PG' => array(
	        'country_name' => 'PAPUA NEW GUINEA',
	        'dial_code' => '675'
	    ),
	    'PH' => array(
	        'country_name' => 'PHILIPPINES',
	        'dial_code' => '63'
	    ),
	    'PK' => array(
	        'country_name' => 'PAKISTAN',
	        'dial_code' => '92'
	    ),
	    'PL' => array(
	        'country_name' => 'POLAND',
	        'dial_code' => '48'
	    ),
	    'PM' => array(
	        'country_name' => 'SAINT PIERRE AND MIQUELON',
	        'dial_code' => '508'
	    ),
	    'PN' => array(
	        'country_name' => 'PITCAIRN',
	        'dial_code' => '870'
	    ),
	    'PR' => array(
	        'country_name' => 'PUERTO RICO',
	        'dial_code' => '1'
	    ),
	    'PT' => array(
	        'country_name' => 'PORTUGAL',
	        'dial_code' => '351'
	    ),
	    'PW' => array(
	        'country_name' => 'PALAU',
	        'dial_code' => '680'
	    ),
	    'PY' => array(
	        'country_name' => 'PARAGUAY',
	        'dial_code' => '595'
	    ),
	    'QA' => array(
	        'country_name' => 'QATAR',
	        'dial_code' => '974'
	    ),
	    'RO' => array(
	        'country_name' => 'ROMANIA',
	        'dial_code' => '40'
	    ),
	    'RS' => array(
	        'country_name' => 'SERBIA',
	        'dial_code' => '381'
	    ),
	    'RU' => array(
	        'country_name' => 'RUSSIAN FEDERATION',
	        'dial_code' => '7'
	    ),
	    'RW' => array(
	        'country_name' => 'RWANDA',
	        'dial_code' => '250'
	    ),
	    'SA' => array(
	        'country_name' => 'SAUDI ARABIA',
	        'dial_code' => '966'
	    ),
	    'SB' => array(
	        'country_name' => 'SOLOMON ISLANDS',
	        'dial_code' => '677'
	    ),
	    'SC' => array(
	        'country_name' => 'SEYCHELLES',
	        'dial_code' => '248'
	    ),
	    'SD' => array(
	        'country_name' => 'SUDAN',
	        'dial_code' => '249'
	    ),
	    'SE' => array(
	        'country_name' => 'SWEDEN',
	        'dial_code' => '46'
	    ),
	    'SG' => array(
	        'country_name' => 'SINGAPORE',
	        'dial_code' => '65'
	    ),
	    'SH' => array(
	        'country_name' => 'SAINT HELENA',
	        'dial_code' => '290'
	    ),
	    'SI' => array(
	        'country_name' => 'SLOVENIA',
	        'dial_code' => '386'
	    ),
	    'SK' => array(
	        'country_name' => 'SLOVAKIA',
	        'dial_code' => '421'
	    ),
	    'SL' => array(
	        'country_name' => 'SIERRA LEONE',
	        'dial_code' => '232'
	    ),
	    'SM' => array(
	        'country_name' => 'SAN MARINO',
	        'dial_code' => '378'
	    ),
	    'SN' => array(
	        'country_name' => 'SENEGAL',
	        'dial_code' => '221'
	    ),
	    'SO' => array(
	        'country_name' => 'SOMALIA',
	        'dial_code' => '252'
	    ),
	    'SR' => array(
	        'country_name' => 'SURINAME',
	        'dial_code' => '597'
	    ),
	    'ST' => array(
	        'country_name' => 'SAO TOME AND PRINCIPE',
	        'dial_code' => '239'
	    ),
	    'SV' => array(
	        'country_name' => 'EL SALVADOR',
	        'dial_code' => '503'
	    ),
	    'SY' => array(
	        'country_name' => 'SYRIAN ARAB REPUBLIC',
	        'dial_code' => '963'
	    ),
	    'SZ' => array(
	        'country_name' => 'SWAZILAND',
	        'dial_code' => '268'
	    ),
	    'TC' => array(
	        'country_name' => 'TURKS AND CAICOS ISLANDS',
	        'dial_code' => '1649'
	    ),
	    'TD' => array(
	        'country_name' => 'CHAD',
	        'dial_code' => '235'
	    ),
	    'TG' => array(
	        'country_name' => 'TOGO',
	        'dial_code' => '228'
	    ),
	    'TH' => array(
	        'country_name' => 'THAILAND',
	        'dial_code' => '66'
	    ),
	    'TJ' => array(
	        'country_name' => 'TAJIKISTAN',
	        'dial_code' => '992'
	    ),
	    'TK' => array(
	        'country_name' => 'TOKELAU',
	        'dial_code' => '690'
	    ),
	    'TL' => array(
	        'country_name' => 'TIMOR-LESTE',
	        'dial_code' => '670'
	    ),
	    'TM' => array(
	        'country_name' => 'TURKMENISTAN',
	        'dial_code' => '993'
	    ),
	    'TN' => array(
	        'country_name' => 'TUNISIA',
	        'dial_code' => '216'
	    ),
	    'TO' => array(
	        'country_name' => 'TONGA',
	        'dial_code' => '676'
	    ),
	    'TR' => array(
	        'country_name' => 'TURKEY',
	        'dial_code' => '90'
	    ),
	    'TT' => array(
	        'country_name' => 'TRINIDAD AND TOBAGO',
	        'dial_code' => '1868'
	    ),
	    'TV' => array(
	        'country_name' => 'TUVALU',
	        'dial_code' => '688'
	    ),
	    'TW' => array(
	        'country_name' => 'TAIWAN, PROVINCE OF CHINA',
	        'dial_code' => '886'
	    ),
	    'TZ' => array(
	        'country_name' => 'TANZANIA, UNITED REPUBLIC OF',
	        'dial_code' => '255'
	    ),
	    'UA' => array(
	        'country_name' => 'UKRAINE',
	        'dial_code' => '380'
	    ),
	    'UG' => array(
	        'country_name' => 'UGANDA',
	        'dial_code' => '256'
	    ),
	    'US' => array(
	        'country_name' => 'UNITED STATES',
	        'dial_code' => '1'
	    ),
	    'UY' => array(
	        'country_name' => 'URUGUAY',
	        'dial_code' => '598'
	    ),
	    'UZ' => array(
	        'country_name' => 'UZBEKISTAN',
	        'dial_code' => '998'
	    ),
	    'VA' => array(
	        'country_name' => 'HOLY SEE (VATICAN CITY STATE)',
	        'dial_code' => '39'
	    ),
	    'VC' => array(
	        'country_name' => 'SAINT VINCENT AND THE GRENADINES',
	        'dial_code' => '1784'
	    ),
	    'VE' => array(
	        'country_name' => 'VENEZUELA',
	        'dial_code' => '58'
	    ),
	    'VG' => array(
	        'country_name' => 'VIRGIN ISLANDS, BRITISH',
	        'dial_code' => '1284'
	    ),
	    'VI' => array(
	        'country_name' => 'VIRGIN ISLANDS, U.S.',
	        'dial_code' => '1340'
	    ),
	    'VN' => array(
	        'country_name' => 'VIET NAM',
	        'dial_code' => '84'
	    ),
	    'VU' => array(
	        'country_name' => 'VANUATU',
	        'dial_code' => '678'
	    ),
	    'WF' => array(
	        'country_name' => 'WALLIS AND FUTUNA',
	        'dial_code' => '681'
	    ),
	    'WS' => array(
	        'country_name' => 'SAMOA',
	        'dial_code' => '685'
	    ),
	    'YE' => array(
	        'country_name' => 'YEMEN',
	        'dial_code' => '967'
	    ),
	    'YT' => array(
	        'country_name' => 'MAYOTTE',
	        'dial_code' => '262'
	    ),
	    'ZA' => array(
	        'country_name' => 'SOUTH AFRICA',
	        'dial_code' => '27'
	    ),
	    'ZM' => array(
	        'country_name' => 'ZAMBIA',
	        'dial_code' => '260'
	    ),
	    'ZW' => array(
	        'country_name' => 'ZIMBABWE',
	        'dial_code' => '263'
	    )
	);
	
	//Return
	return ( $filter == '' ) ? $countryArray : (
	    isset($countryArray[$filter]) ? $countryArray[$filter] : ''
	);
}

function getCountryDialCode() {
	
	$countryArray = getCountryInformation();
	foreach($countryArray as $key => $country)
	{
		$dialCode[$key] = $country['country_name'].' (+'.$country['dial_code'].')';
	}
	return $dialCode;
}

/* End of file country_dial_code_helper.php */
/* Location: ./application/helpers/country_dial_code_helper.php */