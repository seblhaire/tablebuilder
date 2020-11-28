<?php
namespace Seblhaire\TableBuilder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Seblhaire\TableBuilder\Models\Test;
use Illuminate\Database\Schema\Blueprint;
use Seblhaire\TableBuilder\TableBuilderHelper;

/**
 * Description of NumerosController
 *
 * @author seb
 */
class TableController extends \Illuminate\Routing\Controller
{

    /**
     * Controller to display example tables in a page
     *
     * @return View
     */
    public function index()
    {
        // Fist table: contains every column type. Retrieves data from a database contained in memory
        $oTable = TableBuilderHelper::initTable('tabtest', route("tabletest"), array(
            'buttons' => [
                [
                    'id' => 'toto',
                    'em' => 'fas fa-search',
                    'action' => "multiselect",
                    'text' => 'Test multselect'
                ]
            ],
            'itemsperpage' => 20,
            'eltsPerPageChngCallback' => 'eltspagechanged',
            'aftertableload' => 'aftertableload'
        ));
        $oTable->addColumn(TableBuilderHelper::initColumn('numeric', 'id', array(
            'title' => 'Id',
            'completetitle' => 'Employee id',
            'sortable' => true
        )));
        $oTable->addColumn(TableBuilderHelper::initColumn('data', 'lastname', array(
            'title' => 'Last name',
            'sortable' => true,
            'defaultOrder' => 'asc',
            'customAsc' => 'lastname:asc;firstname:asc',
            'customDesc' => 'lastname:desc;firstname:desc'
        )));
        $oTable->addColumn(TableBuilderHelper::initColumn('data', 'firstname', array(
            'title' => 'First name'
        )));
        $oTable->addColumn(TableBuilderHelper::initColumn('date', 'birthday', array(
            'title' => 'Birth date',
            'format' => "yyyy-MM-DD",
            'sortable' => true
        )));
        $oTable->addColumn(TableBuilderHelper::initColumn('image', 'avatar', array(
            'title' => 'Avatar',
            'tag' => 'img'
        )));
        $oTable->addColumn(TableBuilderHelper::initColumn('mail', 'email', array(
            'title' => 'Email',
            'sortable' => true
        )));
        $oTable->addColumn(TableBuilderHelper::initColumn('link', 'homepage', array(
            'title' => 'Homepage',
            'sortable' => true
        )));
        $oTable->addColumn(TableBuilderHelper::initColumn('numeric', 'wage', array(
            'title' => 'Wages',
            'decimals' => 2,
            'thousandsep' => "'",
            'currency' => '$',
            'currencyposafter' => false,
            'decimalsep' => '.'
        )));
        $oTable->addColumn(TableBuilderHelper::initColumn('status', 'hasparking', array(
            'title' => 'Parking?',
            'completetitle' => 'Rents a parking space',
            'aIcons' => array(
                "0" => array(
                    'class' => 'fas fa-square',
                    'title' => 'no',
                    'style' => 'color:red'
                ),
                "1" => array(
                    'class' => 'fas fa-square',
                    'title' => 'yes',
                    'style' => 'color:green'
                )
            )
        )));
        $oTable->addColumn(TableBuilderHelper::initColumn('checkbox', 'selected', array(
            'title' => 'HO',
            'completetitle' => 'Home office ?',
            'action' => 'checkboxclick'
        )));
        $oTable->addColumn(TableBuilderHelper::initColumn('action', 'actions', array(
            'title' => 'Actions',
            'actions' => array(
                [
                    'em' => 'far fa-edit',
                    'text' => 'Edit',
                    'js' => "edit(#{id}, #{lastname},#{firstname})"
                ]
            )
        )));
        // Second table. Gets static data that can be sorted, paginated and filtered by search criteria
        $oTable2 = TableBuilderHelper::initTable('tabtest2', route("tabletest2"));
        $oTable2->addColumn(TableBuilderHelper::initColumn('data', 'country', array(
            'title' => 'Country',
            'sortable' => true,
            'defaultOrder' => 'asc'
        )));
        $oTable2->addColumn(TableBuilderHelper::initColumn('data', 'code', array(
            'title' => 'Code',
            'sortable' => true
        )));
        // Second table. Gets all static data without search criteria nor paginations
        $oTable3 = TableBuilderHelper::initTable('tabtest3', route("tabletest2"), array(
            'itemsperpage' => 0,
            'searchable' => false
        ));
        $oTable3->addColumn(TableBuilderHelper::initColumn('data', 'country', array(
            'title' => 'Country'
        )));
        $oTable3->addColumn(TableBuilderHelper::initColumn('data', 'code', array(
            'title' => 'Code'
        )));
        return view('tablebuilder::example', array(
            'oTable' => $oTable,
            'oTable2' => $oTable2,
            'oTable3' => $oTable3
        ));
    }

    /**
     * builds table data for dynamic tables
     *
     * @param Request $request
     *            request object sent to controller
     * @return Json object
     */
    public function loadTable(Request $request)
    {
        // Build table in memory
        Schema::connection('tablebuilder')->create('tests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('lastname');
            $table->string('firstname');
            $table->date('birthday');
            $table->string('avatar');
            $table->string('email');
            $table->string('homepage');
            $table->unsignedDecimal('wage', 8, 2);
            $table->boolean('hasparking');
            $table->timestamps();

            $table->index('created_at');
            $table->index('updated_at');
        });
        // insert data in database
        foreach ($this->getFakeData() as $line) {
            $data = explode(";", $line);
            $test = new Test();
            $test->lastname = $data[0];
            $test->firstname = $data[1];
            $test->avatar = $data[2];
            $test->email = $data[3];
            $test->homepage = $data[4];
            $test->wage = $data[5];
            $test->hasparking = $data[6];
            $test->birthday = $data[7];
            $test->save();
        }
        $test = new Test(); // inits data
        $oTable = TableBuilderHelper::initDataBuilder($request); // init table data builder object
        $oTable->setQuery($test); // passes Eloquent table object to data Builder
                                  // builds a search function for search field
        $wherefn = function ($query) {
            $query->where('lastname', 'like', '%' . $this->searchTerm . '%')
                ->orwhere('firstname', 'like', '%' . $this->searchTerm . '%')
                ->orwhere('email', 'like', '%' . $this->searchTerm . '%');
        };
        // attach search function to databuilder
        $oTable->setSearchFunction($wherefn);
        // adds a field to data (dummy example)
        $oTable->addMethodToDisplay('selected', function ($test) {
            $collection = collect([
                0,
                1
            ]); // dummy example function to issue true or false
            return $collection->random();
        });
        // adds a field to data to set certain data line in red
        $oTable->addMethodToDisplay(config('tablebuilder.table.rowcontextualtrigger'), function ($user) {
            // set a special color for row where user has a big wage
            if ($user->wage > 10000) {
                return 'table-danger';
            }
            return '';
        });
        // Adds a footer after table data, for example global figures, totals etc
        $oTable->setFooter('Footer to be displayed');
        // return data for table
        return $oTable->output();
    }

    /**
     * builds table data for static tables
     *
     * @param Request $request
     *            request object sent to controller
     * @return Json object
     */
    public function loadTable2(Request $request)
    {
        $oTable = TableBuilderHelper::initDataBuilder($request);
        // gets static data and builds datas
        foreach ($this->getCountryList() as $item) {
            $oTable->addLine($item);
        }
        // builds a search function for search field
        $wherefn = function ($data) {
            return (mb_stripos($data['code'], $this->searchTerm) !== false) || (mb_stripos($data['country'], $this->searchTerm) !== false);
        };
        // attach search function to databuilder
        $oTable->setSearchFunction($wherefn);
        // return data for table
        return $oTable->output();
    }

    /**
     * gets data to be inserted staticaly in example table
     *
     * @return [type] [description]
     */
    private function getCountryList()
    {
        $data = [
            'AD' => 'Andorra',
            'AE' => 'United Arab Emirates',
            'AF' => 'Afghanistan',
            'AG' => 'Antigua and Barbuda',
            'AI' => 'Anguilla',
            'AL' => 'Albania',
            'AM' => 'Armenia',
            'AO' => 'Angola',
            'AQ' => 'Antarctica',
            'AR' => 'Argentina',
            'AS' => 'American Samoa',
            'AT' => 'Austria',
            'AU' => 'Australia',
            'AW' => 'Aruba',
            'AX' => 'Åland Islands',
            'AZ' => 'Azerbaijan',
            'BA' => 'Bosnia and Herzegovina',
            'BB' => 'Barbados',
            'BD' => 'Bangladesh',
            'BE' => 'Belgium',
            'BF' => 'Burkina Faso',
            'BG' => 'Bulgaria',
            'BH' => 'Bahrain',
            'BI' => 'Burundi',
            'BJ' => 'Benin',
            'BL' => 'Saint Barthélemy',
            'BM' => 'Bermuda',
            'BO' => 'Bolivia',
            'BQ' => 'Bonaire, Sint Eustatius and Saba',
            'BR' => 'Brazil',
            'BS' => 'Bahamas',
            'BT' => 'Bhutan',
            'BV' => 'Bouvet Island',
            'BW' => 'Botswana',
            'BY' => 'Belarus',
            'BZ' => 'Belize',
            'CA' => 'Canada',
            'CC' => 'Cocos (Keeling) Islands',
            'CD' => 'Congo, Democratic Republic of the',
            'CF' => 'Central African Republic',
            'CG' => 'Congo',
            'CH' => 'Switzerland',
            'CI' => "Ivory Coast",
            'CK' => 'Cook Islands',
            'CL' => 'Chile',
            'CM' => 'Cameroon',
            'CN' => 'China',
            'CO' => 'Colombia',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'CV' => 'Cabo Verde',
            'CW' => 'Curaçao',
            'CX' => 'Christmas Island',
            'CY' => 'Cyprus',
            'CZ' => 'Czechia',
            'DE' => 'Germany',
            'DJ' => 'Djibouti',
            'DK' => 'Denmark',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'DZ' => 'Algeria',
            'EC' => 'Ecuador',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'EH' => 'Western Sahara',
            'ER' => 'Eritrea',
            'ES' => 'Spain',
            'ET' => 'Ethiopia',
            'FI' => 'Finland',
            'FJ' => 'Fiji',
            'FK' => 'Falkland Islands (Malvinas)',
            'FM' => 'Micronesia',
            'FO' => 'Faroe Islands',
            'FR' => 'France',
            'GA' => 'Gabon',
            'GB' => 'United Kingdom of Great Britain and Northern Ireland',
            'GD' => 'Grenada',
            'GE' => 'Georgia',
            'GF' => 'French Guiana',
            'GG' => 'Guernsey',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GL' => 'Greenland',
            'GM' => 'Gambia',
            'GN' => 'Guinea',
            'GP' => 'Guadeloupe',
            'GQ' => 'Equatorial Guinea',
            'GR' => 'Greece',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'GT' => 'Guatemala',
            'GU' => 'Guam',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HK' => 'Hong Kong',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HR' => 'Croatia',
            'HT' => 'Haiti',
            'HU' => 'Hungary',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IM' => 'Isle of Man',
            'IN' => 'India',
            'IO' => 'British Indian Ocean Territory',
            'IQ' => 'Iraq',
            'IR' => 'Iran',
            'IS' => 'Iceland',
            'IT' => 'Italy',
            'JE' => 'Jersey',
            'JM' => 'Jamaica',
            'JO' => 'Jordan',
            'JP' => 'Japan',
            'KE' => 'Kenya',
            'KG' => 'Kyrgyzstan',
            'KH' => 'Cambodia',
            'KI' => 'Kiribati',
            'KM' => 'Comoros',
            'KN' => 'Saint Kitts and Nevis',
            'KP' => 'North Korea',
            'KR' => 'South Korea',
            'KW' => 'Kuwait',
            'KY' => 'Cayman Islands',
            'KZ' => 'Kazakhstan',
            'LA' => 'Laos',
            'LB' => 'Lebanon',
            'LC' => 'Saint Lucia',
            'LI' => 'Liechtenstein',
            'LK' => 'Sri Lanka',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LV' => 'Latvia',
            'LY' => 'Libya',
            'MA' => 'Morocco',
            'MC' => 'Monaco',
            'MD' => 'Moldova',
            'ME' => 'Montenegro',
            'MF' => 'Saint Martin',
            'MG' => 'Madagascar',
            'MH' => 'Marshall Islands',
            'MK' => 'North Macedonia',
            'ML' => 'Mali',
            'MM' => 'Myanmar',
            'MN' => 'Mongolia',
            'MO' => 'Macao',
            'MP' => 'Northern Mariana Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MS' => 'Montserrat',
            'MT' => 'Malta',
            'MU' => 'Mauritius',
            'MV' => 'Maldives',
            'MW' => 'Malawi',
            'MX' => 'Mexico',
            'MY' => 'Malaysia',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'NC' => 'New Caledonia',
            'NE' => 'Niger',
            'NF' => 'Norfolk Island',
            'NG' => 'Nigeria',
            'NI' => 'Nicaragua',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'NP' => 'Nepal',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'NZ' => 'New Zealand',
            'OM' => 'Oman',
            'PA' => 'Panama',
            'PE' => 'Peru',
            'PF' => 'French Polynesia',
            'PG' => 'Papua New Guinea',
            'PH' => 'Philippines',
            'PK' => 'Pakistan',
            'PL' => 'Poland',
            'PM' => 'Saint Pierre and Miquelon',
            'PN' => 'Pitcairn',
            'PR' => 'Puerto Rico',
            'PS' => 'Palestine',
            'PT' => 'Portugal',
            'PW' => 'Palau',
            'PY' => 'Paraguay',
            'QA' => 'Qatar',
            'RE' => 'Réunion',
            'RO' => 'Romania',
            'RS' => 'Serbia',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'SA' => 'Saudi Arabia',
            'SB' => 'Solomon Islands',
            'SC' => 'Seychelles',
            'SD' => 'Sudan',
            'SE' => 'Sweden',
            'SG' => 'Singapore',
            'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
            'SI' => 'Slovenia',
            'SJ' => 'Svalbard and Jan Mayen',
            'SK' => 'Slovakia',
            'SL' => 'Sierra Leone',
            'SM' => 'San Marino',
            'SN' => 'Senegal',
            'SO' => 'Somalia',
            'SR' => 'Suriname',
            'SS' => 'South Sudan',
            'ST' => 'Sao Tome and Principe',
            'SV' => 'El Salvador',
            'SX' => 'Sint Maarten',
            'SY' => 'Syria',
            'SZ' => 'Eswatini',
            'TC' => 'Turks and Caicos Islands',
            'TD' => 'Chad',
            'TF' => 'French Southern Territories',
            'TG' => 'Togo',
            'TH' => 'Thailand',
            'TJ' => 'Tajikistan',
            'TK' => 'Tokelau',
            'TL' => 'Timor-Leste',
            'TM' => 'Turkmenistan',
            'TN' => 'Tunisia',
            'TO' => 'Tonga',
            'TR' => 'Turkey',
            'TT' => 'Trinidad and Tobago',
            'TV' => 'Tuvalu',
            'TW' => 'Taiwan',
            'TZ' => 'Tanzania',
            'UA' => 'Ukraine',
            'UG' => 'Uganda',
            'UM' => 'United States Minor Outlying Islands',
            'US' => 'United States of America',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VA' => 'Holy See',
            'VC' => 'Saint Vincent and the Grenadines',
            'VE' => 'Venezuela',
            'VG' => 'Virgin Islands (British)',
            'VI' => 'Virgin Islands (U.S.)',
            'VN' => 'Viet Nam',
            'VU' => 'Vanuatu',
            'WF' => 'Wallis and Futuna',
            'WS' => 'Samoa',
            'YE' => 'Yemen',
            'YT' => 'Mayotte',
            'ZA' => 'South Africa',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe'
        ];
        $result = [];
        foreach ($data as $code => $country) {
            $result[] = [
                'code' => $code,
                'country' => $country
            ];
        }
        shuffle($result);
        return $result;
    }

    /**
     * returns data to be inserted in temporary database in memory
     *
     * @return array strings in csv format
     */
    private function getFakeData()
    {
        return [
            'Yundt;Antonina;https://lorempixel.com/25/25/?20106;zkonopelski@deckow.com;http://fadel.biz/tenetur-nobis-voluptas-impedit-quam-corrupti-ipsum-necessitatibus-quia;5250.75;1;1984-04-05;',
            'Stiedemann;Vaughn;https://lorempixel.com/25/25/?32440;rosalyn08@mclaughlin.com;http://marvin.com/;7319.49;0;1983-01-27;',
            'Botsford;Candice;https://lorempixel.com/25/25/?75501;gleason.krista@rogahn.com;http://ullrich.com/voluptatem-iusto-blanditiis-esse-ipsam-neque-quasi.html;7199.77;0;1981-05-02;',
            'Labadie;Toney;https://lorempixel.com/25/25/?72071;dulce52@dickinson.com;http://bartoletti.com/nesciunt-harum-at-eligendi-debitis-laboriosam-quos;8526.88;0;1994-07-14;',
            'Bins;Cleora;https://lorempixel.com/25/25/?22402;rylee98@abernathy.com;http://walker.info/dolorem-deleniti-earum-error;5561.32;0;2000-09-12;',
            'O\'Hara;Bart;https://lorempixel.com/25/25/?44313;rice.franz@baumbach.com;http://www.gleichner.info/rerum-qui-itaque-doloremque-laboriosam-consectetur-eum-aut.html;6012.53;0;1984-03-18;',
            'Terry;Lonzo;https://lorempixel.com/25/25/?18258;stokes.keely@lemke.info;http://maggio.com/voluptatem-explicabo-doloremque-ad-omnis;10321.80;1;1993-05-06;',
            'Kertzmann;Georgette;https://lorempixel.com/25/25/?85382;reilly.gaston@bergnaum.net;http://www.batz.com/;5913.76;1;1989-07-08;',
            'Trantow;Brionna;https://lorempixel.com/25/25/?76734;hweber@hartmann.info;http://www.cole.com/id-et-consequatur-aut-ut;5754.70;0;1962-12-27;',
            'Ferry;Nya;https://lorempixel.com/25/25/?81038;nhane@wuckert.com;http://schoen.org/velit-consequuntur-ex-voluptatem-et;10525.83;1;1968-05-31;',
            'Bauch;Elnora;https://lorempixel.com/25/25/?36154;finn.spencer@terry.com;http://bogan.biz/et-hic-odio-explicabo-dolorem-distinctio-nobis;6641.81;1;1980-03-27;',
            'Welch;Loy;https://lorempixel.com/25/25/?41573;yhammes@mcglynn.info;http://www.gulgowski.net/;10577.02;1;1995-03-21;',
            'Towne;Lee;https://lorempixel.com/25/25/?81780;glover.alta@greenholt.net;https://ward.com/maxime-esse-hic-provident-at.html;9297.04;0;1984-01-01;',
            'Kreiger;Rene;https://lorempixel.com/25/25/?45353;yrohan@ziemann.info;http://www.paucek.com/;11059.80;0;1985-10-13;',
            'Kreiger;Brady;https://lorempixel.com/25/25/?91607;imelda.boyle@bashirian.com;http://nienow.org/quia-earum-praesentium-quisquam-porro-pariatur-ut;7138.12;1;1977-11-25;',
            'Grady;Anika;https://lorempixel.com/25/25/?42739;candida.gulgowski@homenick.com;http://www.purdy.com/modi-cupiditate-eos-expedita-ut-est-repudiandae-deleniti-qui;5419.72;0;1988-09-17;',
            'Ruecker;Nathanial;https://lorempixel.com/25/25/?49749;jerod04@kiehn.com;https://www.russel.info/quia-repudiandae-illum-sit-rerum-eos-nisi;10368.11;1;1990-10-11;',
            'Legros;Angelo;https://lorempixel.com/25/25/?53486;bparker@botsford.com;https://jakubowski.org/cupiditate-ullam-officia-consequuntur-id-aliquid.html;8350.48;1;1974-10-02;',
            'Fay;Kaitlyn;https://lorempixel.com/25/25/?55500;zcronin@ebert.com;http://wuckert.com/neque-modi-voluptates-autem-officia-earum-molestiae-atque-vero;11974.99;1;1990-08-30;',
            'Carter;Kaylah;https://lorempixel.com/25/25/?17078;jortiz@crist.info;http://www.oberbrunner.net/ut-sed-iste-quia;9365.22;1;1971-04-28;',
            'Feil;Jason;https://lorempixel.com/25/25/?63817;xander.sporer@dubuque.com;http://wehner.com/minima-repellat-vel-porro-eum;7818.64;0;1986-09-01;',
            'Dietrich;Evert;https://lorempixel.com/25/25/?77783;kieran32@cremin.net;http://www.bauch.info/ut-eaque-dolore-fugit-exercitationem;7565.00;1;1987-09-14;',
            'Jones;Meda;https://lorempixel.com/25/25/?64256;slesch@swift.org;http://senger.com/placeat-tempora-et-ullam-debitis.html;8045.71;0;1982-04-28;',
            'Daugherty;Daija;https://lorempixel.com/25/25/?40424;tdaniel@gerhold.com;https://www.white.info/deserunt-molestiae-ipsum-fugiat-sit-quaerat-assumenda;9136.15;0;1987-04-07;',
            'Lowe;Buford;https://lorempixel.com/25/25/?23033;lila51@schowalter.net;http://www.gusikowski.org/consectetur-maiores-accusantium-necessitatibus-quis-vitae-et-rerum-pariatur;5360.36;1;1995-03-10;',
            'Torphy;Gregoria;https://lorempixel.com/25/25/?91304;ggleichner@kiehn.net;http://www.little.com/aut-delectus-minima-excepturi-voluptatem-nobis-consequatur;10168.23;1;1998-07-30;',
            'West;Conor;https://lorempixel.com/25/25/?59248;vupton@dach.info;http://rowe.com/sit-eum-autem-magnam.html;11053.80;1;1962-06-30;',
            'Fahey;Alfreda;https://lorempixel.com/25/25/?70592;eladio.simonis@wolf.org;http://zboncak.com/;6251.97;1;1964-12-27;',
            'O\'Keefe;Laurine;https://lorempixel.com/25/25/?25765;oschimmel@langosh.biz;http://www.bergstrom.com/doloribus-voluptatibus-consequatur-sed-sequi.html;6355.13;1;1962-09-09;',
            'Jaskolski;Issac;https://lorempixel.com/25/25/?44172;price.wisozk@stehr.com;http://www.nikolaus.com/eligendi-aliquam-dolor-sit-suscipit-sit-ipsam-maiores;10742.25;1;1983-08-31;',
            'Wuckert;Karley;https://lorempixel.com/25/25/?68372;roberts.bruce@waters.org;http://www.mcglynn.com/quos-dolorem-est-dicta-in-quia-laboriosam-qui.html;9193.70;1;1989-09-19;',
            'Spinka;Jairo;https://lorempixel.com/25/25/?73559;destiny44@schultz.net;http://www.altenwerth.org/laborum-qui-ut-aut-porro-suscipit;8787.83;0;1992-05-24;',
            'Klocko;Frederique;https://lorempixel.com/25/25/?99608;alexander62@schmeler.com;http://leannon.com/non-enim-ea-laborum-aut-et-dignissimos.html;7577.00;1;1988-08-06;',
            'Lueilwitz;Favian;https://lorempixel.com/25/25/?57058;powlowski.lavonne@wintheiser.com;http://www.kiehn.com/;11743.80;0;1982-06-02;',
            'Grant;Ignacio;https://lorempixel.com/25/25/?18047;windler.lauretta@reichert.com;http://schulist.com/sed-consequatur-voluptatem-eos-est;7231.74;1;1994-02-14;',
            'Schulist;Bridie;https://lorempixel.com/25/25/?76101;brisa14@dare.org;http://medhurst.com/dolor-modi-aspernatur-omnis-molestias;11374.57;1;1984-01-22;',
            'Altenwerth;Tyra;https://lorempixel.com/25/25/?47468;misael89@walker.com;http://www.parker.net/id-ut-accusantium-aut-voluptate-labore-natus-architecto-veniam;10628.23;0;1988-01-14;',
            'Casper;Alana;https://lorempixel.com/25/25/?51469;friedrich.wiegand@dubuque.com;http://www.brekke.com/;8550.67;0;1963-10-01;',
            'Kovacek;Kelsi;https://lorempixel.com/25/25/?68118;mhauck@daugherty.com;http://koch.com/aut-quia-earum-explicabo-aut-corrupti-reprehenderit.html;7082.08;0;1968-05-28;',
            'Douglas;Rollin;https://lorempixel.com/25/25/?44270;tlowe@littel.biz;http://conroy.org/aut-aspernatur-distinctio-sapiente-nihil-inventore.html;7234.10;1;1984-06-07;',
            'Brekke;Kimberly;https://lorempixel.com/25/25/?99783;rcollins@schultz.com;http://crooks.org/nemo-ducimus-illo-dicta-vero.html;7866.61;0;1991-04-24;',
            'Gerlach;Karlee;https://lorempixel.com/25/25/?87342;mccullough.valerie@swaniawski.org;https://boyle.com/iste-libero-omnis-est-labore-sint.html;4662.13;1;2001-09-23;',
            'Will;Quinton;https://lorempixel.com/25/25/?28619;jrohan@mills.com;http://www.schmidt.info/quia-porro-et-iure-quia;10531.18;0;1994-11-10;',
            'Parker;Emmett;https://lorempixel.com/25/25/?18038;jbarton@powlowski.org;https://corwin.com/quis-quas-rerum-quis-delectus-facere-est-sapiente.html;9969.50;0;1978-08-08;',
            'Lueilwitz;Anastasia;https://lorempixel.com/25/25/?70482;frank01@bogisich.com;http://hoppe.com/;10477.39;0;1972-05-26;',
            'Berge;Hannah;https://lorempixel.com/25/25/?19729;qmoore@kassulke.biz;http://www.mayer.com/consequatur-repellat-dolores-explicabo-numquam-soluta-qui-repellat;7360.85;1;1984-11-21;',
            'Breitenberg;Jarrell;https://lorempixel.com/25/25/?76656;runolfsson.alexandro@ward.com;http://www.friesen.com/vel-doloremque-quasi-explicabo;7991.48;0;1984-08-22;',
            'Miller;Cheyenne;https://lorempixel.com/25/25/?41327;sdamore@dicki.org;http://www.gulgowski.net/;9523.31;1;1978-08-01;',
            'Auer;Arnulfo;https://lorempixel.com/25/25/?34317;sigmund.moore@quitzon.com;https://prosacco.org/illum-voluptatum-consequatur-est-consequatur.html;5735.19;0;1994-05-06;',
            'Bechtelar;Danielle;https://lorempixel.com/25/25/?19292;yschaden@fahey.com;https://leffler.com/voluptatem-labore-assumenda-est-ut-ea-odio.html;9906.70;0;1995-01-08;'
        ];
    }
}
