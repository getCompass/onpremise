<?php

namespace BaseFrame\Conf;

use BaseFrame\Exception\Domain\CountryNotFound;
use BaseFrame\Struct\Country\ConfigItem;

/**
 * Класс со странами
 */
class Country {

	protected const _COUNTRY_LIST = [
		"af" =>
			[
				"name"                  => "Afghanistan",
				"flag_emoji_short_name" => ":flag-af:",
				"phone_prefix_list"     =>
					[
						0 => "+93",
					],
			],
		"al" =>
			[
				"name"                  => "Albania",
				"flag_emoji_short_name" => ":flag-al:",
				"phone_prefix_list"     =>
					[
						0 => "+355",
					],
			],
		"dz" =>
			[
				"name"                  => "Algeria",
				"flag_emoji_short_name" => ":flag-dz:",
				"phone_prefix_list"     =>
					[
						0 => "+213",
					],
			],
		"as" =>
			[
				"name"                  => "American Samoa",
				"flag_emoji_short_name" => ":flag-as:",
				"phone_prefix_list"     =>
					[
						0 => "+1684",
					],
			],
		"ad" =>
			[
				"name"                  => "Andorra",
				"flag_emoji_short_name" => ":flag-ad:",
				"phone_prefix_list"     =>
					[
						0 => "+376",
					],
			],
		"ao" =>
			[
				"name"                  => "Angola",
				"flag_emoji_short_name" => ":flag-ao:",
				"phone_prefix_list"     =>
					[
						0 => "+244",
					],
			],
		"ai" =>
			[
				"name"                  => "Anguilla",
				"flag_emoji_short_name" => ":flag-ai:",
				"phone_prefix_list"     =>
					[
						0 => "+1264",
					],
			],
		"aq" =>
			[
				"name"                  => "Antarctica",
				"flag_emoji_short_name" => ":flag-aq:",
				"phone_prefix_list"     =>
					[
						0 => "+672",
					],
			],
		"ag" =>
			[
				"name"                  => "Antigua and Barbuda",
				"flag_emoji_short_name" => ":flag-ag:",
				"phone_prefix_list"     =>
					[
						0 => "+1268",
					],
			],
		"ar" =>
			[
				"name"                  => "Argentina",
				"flag_emoji_short_name" => ":flag-ar:",
				"phone_prefix_list"     =>
					[
						0 => "+54",
					],
			],
		"am" =>
			[
				"name"                  => "Armenia",
				"flag_emoji_short_name" => ":flag-am:",
				"phone_prefix_list"     =>
					[
						0 => "+374",
					],
			],
		"aw" =>
			[
				"name"                  => "Aruba",
				"flag_emoji_short_name" => ":flag-aw:",
				"phone_prefix_list"     =>
					[
						0 => "+297",
					],
			],
		"au" =>
			[
				"name"                  => "Australia",
				"flag_emoji_short_name" => ":flag-au:",
				"phone_prefix_list"     =>
					[
						0 => "+61",
					],
			],
		"at" =>
			[
				"name"                  => "Austria",
				"flag_emoji_short_name" => ":flag-at:",
				"phone_prefix_list"     =>
					[
						0 => "+43",
					],
			],
		"az" =>
			[
				"name"                  => "Azerbaijan",
				"flag_emoji_short_name" => ":flag-az:",
				"phone_prefix_list"     =>
					[
						0 => "+994",
					],
			],
		"bs" =>
			[
				"name"                  => "Bahamas",
				"flag_emoji_short_name" => ":flag-bs:",
				"phone_prefix_list"     =>
					[
						0 => "+1242",
					],
			],
		"bh" =>
			[
				"name"                  => "Bahrain",
				"flag_emoji_short_name" => ":flag-bh:",
				"phone_prefix_list"     =>
					[
						0 => "+973",
					],
			],
		"bd" =>
			[
				"name"                  => "Bangladesh",
				"flag_emoji_short_name" => ":flag-bd:",
				"phone_prefix_list"     =>
					[
						0 => "+880",
					],
			],
		"bb" =>
			[
				"name"                  => "Barbados",
				"flag_emoji_short_name" => ":flag-bb:",
				"phone_prefix_list"     =>
					[
						0 => "+1246",
					],
			],
		"by" =>
			[
				"name"                  => "Belarus",
				"flag_emoji_short_name" => ":flag-by:",
				"phone_prefix_list"     =>
					[
						0 => "+375",
					],
			],
		"be" =>
			[
				"name"                  => "Belgium",
				"flag_emoji_short_name" => ":flag-be:",
				"phone_prefix_list"     =>
					[
						0 => "+32",
					],
			],
		"bz" =>
			[
				"name"                  => "Belize",
				"flag_emoji_short_name" => ":flag-bz:",
				"phone_prefix_list"     =>
					[
						0 => "+501",
					],
			],
		"bj" =>
			[
				"name"                  => "Benin",
				"flag_emoji_short_name" => ":flag-bj:",
				"phone_prefix_list"     =>
					[
						0 => "+229",
					],
			],
		"bm" =>
			[
				"name"                  => "Bermuda",
				"flag_emoji_short_name" => ":flag-bm:",
				"phone_prefix_list"     =>
					[
						0 => "+1441",
					],
			],
		"bt" =>
			[
				"name"                  => "Bhutan",
				"flag_emoji_short_name" => ":flag-bt:",
				"phone_prefix_list"     =>
					[
						0 => "+975",
					],
			],
		"bo" =>
			[
				"name"                  => "Bolivia",
				"flag_emoji_short_name" => ":flag-bo:",
				"phone_prefix_list"     =>
					[
						0 => "+591",
					],
			],
		"ba" =>
			[
				"name"                  => "Bosnia and Herzegovina",
				"flag_emoji_short_name" => ":flag-ba:",
				"phone_prefix_list"     =>
					[
						0 => "+387",
					],
			],
		"bw" =>
			[
				"name"                  => "Botswana",
				"flag_emoji_short_name" => ":flag-bw:",
				"phone_prefix_list"     =>
					[
						0 => "+267",
					],
			],
		"br" =>
			[
				"name"                  => "Brazil",
				"flag_emoji_short_name" => ":flag-br:",
				"phone_prefix_list"     =>
					[
						0 => "+55",
					],
			],
		"io" =>
			[
				"name"                  => "British Indian Ocean Territory",
				"flag_emoji_short_name" => ":flag-io:",
				"phone_prefix_list"     =>
					[
						0 => "+246",
					],
			],
		"vg" =>
			[
				"name"                  => "British Virgin Islands",
				"flag_emoji_short_name" => ":flag-vg:",
				"phone_prefix_list"     =>
					[
						0 => "+1284",
					],
			],
		"bn" =>
			[
				"name"                  => "Brunei",
				"flag_emoji_short_name" => ":flag-bn:",
				"phone_prefix_list"     =>
					[
						0 => "+673",
					],
			],
		"bg" =>
			[
				"name"                  => "Bulgaria",
				"flag_emoji_short_name" => ":flag-bg:",
				"phone_prefix_list"     =>
					[
						0 => "+359",
					],
			],
		"bf" =>
			[
				"name"                  => "Burkina Faso",
				"flag_emoji_short_name" => ":flag-bf:",
				"phone_prefix_list"     =>
					[
						0 => "+226",
					],
			],
		"bi" =>
			[
				"name"                  => "Burundi",
				"flag_emoji_short_name" => ":flag-bi:",
				"phone_prefix_list"     =>
					[
						0 => "+257",
					],
			],
		"kh" =>
			[
				"name"                  => "Cambodia",
				"flag_emoji_short_name" => ":flag-kh:",
				"phone_prefix_list"     =>
					[
						0 => "+855",
					],
			],
		"cm" =>
			[
				"name"                  => "Cameroon",
				"flag_emoji_short_name" => ":flag-cm:",
				"phone_prefix_list"     =>
					[
						0 => "+237",
					],
			],
		"ca" =>
			[
				"name"                  => "Canada",
				"flag_emoji_short_name" => ":flag-ca:",
				"phone_prefix_list"     =>
					[
						0 => "+1",
					],
			],
		"cv" =>
			[
				"name"                  => "Cape Verde",
				"flag_emoji_short_name" => ":flag-cv:",
				"phone_prefix_list"     =>
					[
						0 => "+238",
					],
			],
		"ky" =>
			[
				"name"                  => "Cayman Islands",
				"flag_emoji_short_name" => ":flag-ky:",
				"phone_prefix_list"     =>
					[
						0 => "+1345",
					],
			],
		"cf" =>
			[
				"name"                  => "Central African Republic",
				"flag_emoji_short_name" => ":flag-cf:",
				"phone_prefix_list"     =>
					[
						0 => "+236",
					],
			],
		"td" =>
			[
				"name"                  => "Chad",
				"flag_emoji_short_name" => ":flag-td:",
				"phone_prefix_list"     =>
					[
						0 => "+235",
					],
			],
		"cl" =>
			[
				"name"                  => "Chile",
				"flag_emoji_short_name" => ":flag-cl:",
				"phone_prefix_list"     =>
					[
						0 => "+56",
					],
			],
		"cn" =>
			[
				"name"                  => "China",
				"flag_emoji_short_name" => ":flag-cn:",
				"phone_prefix_list"     =>
					[
						0 => "+86",
					],
			],
		"cx" =>
			[
				"name"                  => "Christmas Island",
				"flag_emoji_short_name" => ":flag-cx:",
				"phone_prefix_list"     =>
					[
						0 => "+61",
					],
			],
		"cc" =>
			[
				"name"                  => "Cocos Islands",
				"flag_emoji_short_name" => ":flag-cc:",
				"phone_prefix_list"     =>
					[
						0 => "+61",
					],
			],
		"co" =>
			[
				"name"                  => "Colombia",
				"flag_emoji_short_name" => ":flag-co:",
				"phone_prefix_list"     =>
					[
						0 => "+57",
					],
			],
		"km" =>
			[
				"name"                  => "Comoros",
				"flag_emoji_short_name" => ":flag-km:",
				"phone_prefix_list"     =>
					[
						0 => "+269",
					],
			],
		"ck" =>
			[
				"name"                  => "Cook Islands",
				"flag_emoji_short_name" => ":flag-ck:",
				"phone_prefix_list"     =>
					[
						0 => "+682",
					],
			],
		"cr" =>
			[
				"name"                  => "Costa Rica",
				"flag_emoji_short_name" => ":flag-cr:",
				"phone_prefix_list"     =>
					[
						0 => "+506",
					],
			],
		"hr" =>
			[
				"name"                  => "Croatia",
				"flag_emoji_short_name" => ":flag-hr:",
				"phone_prefix_list"     =>
					[
						0 => "+385",
					],
			],
		"cu" =>
			[
				"name"                  => "Cuba",
				"flag_emoji_short_name" => ":flag-cu:",
				"phone_prefix_list"     =>
					[
						0 => "+53",
					],
			],
		"cw" =>
			[
				"name"                  => "Curacao",
				"flag_emoji_short_name" => ":flag-cw:",
				"phone_prefix_list"     =>
					[
						0 => "+599",
					],
			],
		"cy" =>
			[
				"name"                  => "Cyprus",
				"flag_emoji_short_name" => ":flag-cy:",
				"phone_prefix_list"     =>
					[
						0 => "+357",
					],
			],
		"cz" =>
			[
				"name"                  => "Czech Republic",
				"flag_emoji_short_name" => ":flag-cz:",
				"phone_prefix_list"     =>
					[
						0 => "+420",
					],
			],
		"cd" =>
			[
				"name"                  => "Democratic Republic of the Congo",
				"flag_emoji_short_name" => ":flag-cd:",
				"phone_prefix_list"     =>
					[
						0 => "+243",
					],
			],
		"dk" =>
			[
				"name"                  => "Denmark",
				"flag_emoji_short_name" => ":flag-dk:",
				"phone_prefix_list"     =>
					[
						0 => "+45",
					],
			],
		"dj" =>
			[
				"name"                  => "Djibouti",
				"flag_emoji_short_name" => ":flag-dj:",
				"phone_prefix_list"     =>
					[
						0 => "+253",
					],
			],
		"dm" =>
			[
				"name"                  => "Dominica",
				"flag_emoji_short_name" => ":flag-dm:",
				"phone_prefix_list"     =>
					[
						0 => "+1767",
					],
			],
		"do" =>
			[
				"name"                  => "Dominican Republic",
				"flag_emoji_short_name" => ":flag-do:",
				"phone_prefix_list"     =>
					[
						0 => "+1809",
						1 => "+1829",
						2 => "+1849",
					],
			],
		"tl" =>
			[
				"name"                  => "East Timor",
				"flag_emoji_short_name" => ":flag-tl:",
				"phone_prefix_list"     =>
					[
						0 => "+670",
					],
			],
		"ec" =>
			[
				"name"                  => "Ecuador",
				"flag_emoji_short_name" => ":flag-ec:",
				"phone_prefix_list"     =>
					[
						0 => "+593",
					],
			],
		"eg" =>
			[
				"name"                  => "Egypt",
				"flag_emoji_short_name" => ":flag-eg:",
				"phone_prefix_list"     =>
					[
						0 => "+20",
					],
			],
		"sv" =>
			[
				"name"                  => "El Salvador",
				"flag_emoji_short_name" => ":flag-sv:",
				"phone_prefix_list"     =>
					[
						0 => "+503",
					],
			],
		"gq" =>
			[
				"name"                  => "Equatorial Guinea",
				"flag_emoji_short_name" => ":flag-gq:",
				"phone_prefix_list"     =>
					[
						0 => "+240",
					],
			],
		"er" =>
			[
				"name"                  => "Eritrea",
				"flag_emoji_short_name" => ":flag-er:",
				"phone_prefix_list"     =>
					[
						0 => "+291",
					],
			],
		"ee" =>
			[
				"name"                  => "Estonia",
				"flag_emoji_short_name" => ":flag-ee:",
				"phone_prefix_list"     =>
					[
						0 => "+372",
					],
			],
		"et" =>
			[
				"name"                  => "Ethiopia",
				"flag_emoji_short_name" => ":flag-et:",
				"phone_prefix_list"     =>
					[
						0 => "+251",
					],
			],
		"fk" =>
			[
				"name"                  => "Falkland Islands",
				"flag_emoji_short_name" => ":flag-fk:",
				"phone_prefix_list"     =>
					[
						0 => "+500",
					],
			],
		"fo" =>
			[
				"name"                  => "Faroe Islands",
				"flag_emoji_short_name" => ":flag-fo:",
				"phone_prefix_list"     =>
					[
						0 => "+298",
					],
			],
		"fj" =>
			[
				"name"                  => "Fiji",
				"flag_emoji_short_name" => ":flag-fj:",
				"phone_prefix_list"     =>
					[
						0 => "+679",
					],
			],
		"fi" =>
			[
				"name"                  => "Finland",
				"flag_emoji_short_name" => ":flag-fi:",
				"phone_prefix_list"     =>
					[
						0 => "+358",
					],
			],
		"fr" =>
			[
				"name"                  => "France",
				"flag_emoji_short_name" => ":flag-fr:",
				"phone_prefix_list"     =>
					[
						0 => "+33",
					],
			],
		"pf" =>
			[
				"name"                  => "French Polynesia",
				"flag_emoji_short_name" => ":flag-pf:",
				"phone_prefix_list"     =>
					[
						0 => "+689",
					],
			],
		"ga" =>
			[
				"name"                  => "Gabon",
				"flag_emoji_short_name" => ":flag-ga:",
				"phone_prefix_list"     =>
					[
						0 => "+241",
					],
			],
		"gm" =>
			[
				"name"                  => "Gambia",
				"flag_emoji_short_name" => ":flag-gm:",
				"phone_prefix_list"     =>
					[
						0 => "+220",
					],
			],
		"ge" =>
			[
				"name"                  => "Georgia",
				"flag_emoji_short_name" => ":flag-ge:",
				"phone_prefix_list"     =>
					[
						0 => "+995",
					],
			],
		"de" =>
			[
				"name"                  => "Germany",
				"flag_emoji_short_name" => ":flag-de:",
				"phone_prefix_list"     =>
					[
						0 => "+49",
					],
			],
		"gh" =>
			[
				"name"                  => "Ghana",
				"flag_emoji_short_name" => ":flag-gh:",
				"phone_prefix_list"     =>
					[
						0 => "+233",
					],
			],
		"gi" =>
			[
				"name"                  => "Gibraltar",
				"flag_emoji_short_name" => ":flag-gi:",
				"phone_prefix_list"     =>
					[
						0 => "+350",
					],
			],
		"gr" =>
			[
				"name"                  => "Greece",
				"flag_emoji_short_name" => ":flag-gr:",
				"phone_prefix_list"     =>
					[
						0 => "+30",
					],
			],
		"gl" =>
			[
				"name"                  => "Greenland",
				"flag_emoji_short_name" => ":flag-gl:",
				"phone_prefix_list"     =>
					[
						0 => "+299",
					],
			],
		"gd" =>
			[
				"name"                  => "Grenada",
				"flag_emoji_short_name" => ":flag-gd:",
				"phone_prefix_list"     =>
					[
						0 => "+1473",
					],
			],
		"gu" =>
			[
				"name"                  => "Guam",
				"flag_emoji_short_name" => ":flag-gu:",
				"phone_prefix_list"     =>
					[
						0 => "+1671",
					],
			],
		"gt" =>
			[
				"name"                  => "Guatemala",
				"flag_emoji_short_name" => ":flag-gt:",
				"phone_prefix_list"     =>
					[
						0 => "+502",
					],
			],
		"gg" =>
			[
				"name"                  => "Guernsey",
				"flag_emoji_short_name" => ":flag-gg:",
				"phone_prefix_list"     =>
					[
						0 => "+441481",
					],
			],
		"gn" =>
			[
				"name"                  => "Guinea",
				"flag_emoji_short_name" => ":flag-gn:",
				"phone_prefix_list"     =>
					[
						0 => "+224",
					],
			],
		"gw" =>
			[
				"name"                  => "Guinea-Bissau",
				"flag_emoji_short_name" => ":flag-gw:",
				"phone_prefix_list"     =>
					[
						0 => "+245",
					],
			],
		"gy" =>
			[
				"name"                  => "Guyana",
				"flag_emoji_short_name" => ":flag-gy:",
				"phone_prefix_list"     =>
					[
						0 => "+592",
					],
			],
		"ht" =>
			[
				"name"                  => "Haiti",
				"flag_emoji_short_name" => ":flag-ht:",
				"phone_prefix_list"     =>
					[
						0 => "+509",
					],
			],
		"hn" =>
			[
				"name"                  => "Honduras",
				"flag_emoji_short_name" => ":flag-hn:",
				"phone_prefix_list"     =>
					[
						0 => "+504",
					],
			],
		"hk" =>
			[
				"name"                  => "Hong Kong",
				"flag_emoji_short_name" => ":flag-hk:",
				"phone_prefix_list"     =>
					[
						0 => "+852",
					],
			],
		"hu" =>
			[
				"name"                  => "Hungary",
				"flag_emoji_short_name" => ":flag-hu:",
				"phone_prefix_list"     =>
					[
						0 => "+36",
					],
			],
		"is" =>
			[
				"name"                  => "Iceland",
				"flag_emoji_short_name" => ":flag-is:",
				"phone_prefix_list"     =>
					[
						0 => "+354",
					],
			],
		"in" =>
			[
				"name"                  => "India",
				"flag_emoji_short_name" => ":flag-in:",
				"phone_prefix_list"     =>
					[
						0 => "+91",
					],
			],
		"id" =>
			[
				"name"                  => "Indonesia",
				"flag_emoji_short_name" => ":flag-id:",
				"phone_prefix_list"     =>
					[
						0 => "+62",
					],
			],
		"ir" =>
			[
				"name"                  => "Iran",
				"flag_emoji_short_name" => ":flag-ir:",
				"phone_prefix_list"     =>
					[
						0 => "+98",
					],
			],
		"iq" =>
			[
				"name"                  => "Iraq",
				"flag_emoji_short_name" => ":flag-iq:",
				"phone_prefix_list"     =>
					[
						0 => "+964",
					],
			],
		"ie" =>
			[
				"name"                  => "Ireland",
				"flag_emoji_short_name" => ":flag-ie:",
				"phone_prefix_list"     =>
					[
						0 => "+353",
					],
			],
		"im" =>
			[
				"name"                  => "Isle of Man",
				"flag_emoji_short_name" => ":flag-im:",
				"phone_prefix_list"     =>
					[
						0 => "+441624",
					],
			],
		"il" =>
			[
				"name"                  => "Israel",
				"flag_emoji_short_name" => ":flag-il:",
				"phone_prefix_list"     =>
					[
						0 => "+972",
					],
			],
		"it" =>
			[
				"name"                  => "Italy",
				"flag_emoji_short_name" => ":flag-it:",
				"phone_prefix_list"     =>
					[
						0 => "+39",
					],
			],
		"ci" =>
			[
				"name"                  => "Ivory Coast",
				"flag_emoji_short_name" => ":flag-ci:",
				"phone_prefix_list"     =>
					[
						0 => "+225",
					],
			],
		"jm" =>
			[
				"name"                  => "Jamaica",
				"flag_emoji_short_name" => ":flag-jm:",
				"phone_prefix_list"     =>
					[
						0 => "+1876",
					],
			],
		"jp" =>
			[
				"name"                  => "Japan",
				"flag_emoji_short_name" => ":flag-jp:",
				"phone_prefix_list"     =>
					[
						0 => "+81",
					],
			],
		"je" =>
			[
				"name"                  => "Jersey",
				"flag_emoji_short_name" => ":flag-je:",
				"phone_prefix_list"     =>
					[
						0 => "+441534",
					],
			],
		"jo" =>
			[
				"name"                  => "Jordan",
				"flag_emoji_short_name" => ":flag-jo:",
				"phone_prefix_list"     =>
					[
						0 => "+962",
					],
			],
		"kz" =>
			[
				"name"                  => "Kazakhstan",
				"flag_emoji_short_name" => ":flag-kz:",
				"phone_prefix_list"     =>
					[
						0 => "+7",
						1 => "+997",
					],
			],
		"ke" =>
			[
				"name"                  => "Kenya",
				"flag_emoji_short_name" => ":flag-ke:",
				"phone_prefix_list"     =>
					[
						0 => "+254",
					],
			],
		"ki" =>
			[
				"name"                  => "Kiribati",
				"flag_emoji_short_name" => ":flag-ki:",
				"phone_prefix_list"     =>
					[
						0 => "+686",
					],
			],
		"xk" =>
			[
				"name"                  => "Kosovo",
				"flag_emoji_short_name" => ":flag-xk:",
				"phone_prefix_list"     =>
					[
						0 => "+383",
					],
			],
		"kw" =>
			[
				"name"                  => "Kuwait",
				"flag_emoji_short_name" => ":flag-kw:",
				"phone_prefix_list"     =>
					[
						0 => "+965",
					],
			],
		"kg" =>
			[
				"name"                  => "Kyrgyzstan",
				"flag_emoji_short_name" => ":flag-kg:",
				"phone_prefix_list"     =>
					[
						0 => "+996",
					],
			],
		"la" =>
			[
				"name"                  => "Laos",
				"flag_emoji_short_name" => ":flag-la:",
				"phone_prefix_list"     =>
					[
						0 => "+856",
					],
			],
		"lv" =>
			[
				"name"                  => "Latvia",
				"flag_emoji_short_name" => ":flag-lv:",
				"phone_prefix_list"     =>
					[
						0 => "+371",
					],
			],
		"lb" =>
			[
				"name"                  => "Lebanon",
				"flag_emoji_short_name" => ":flag-lb:",
				"phone_prefix_list"     =>
					[
						0 => "+961",
					],
			],
		"ls" =>
			[
				"name"                  => "Lesotho",
				"flag_emoji_short_name" => ":flag-ls:",
				"phone_prefix_list"     =>
					[
						0 => "+266",
					],
			],
		"lr" =>
			[
				"name"                  => "Liberia",
				"flag_emoji_short_name" => ":flag-lr:",
				"phone_prefix_list"     =>
					[
						0 => "+231",
					],
			],
		"ly" =>
			[
				"name"                  => "Libya",
				"flag_emoji_short_name" => ":flag-ly:",
				"phone_prefix_list"     =>
					[
						0 => "+218",
					],
			],
		"li" =>
			[
				"name"                  => "Liechtenstein",
				"flag_emoji_short_name" => ":flag-li:",
				"phone_prefix_list"     =>
					[
						0 => "+423",
					],
			],
		"lt" =>
			[
				"name"                  => "Lithuania",
				"flag_emoji_short_name" => ":flag-lt:",
				"phone_prefix_list"     =>
					[
						0 => "+370",
					],
			],
		"lu" =>
			[
				"name"                  => "Luxembourg",
				"flag_emoji_short_name" => ":flag-lu:",
				"phone_prefix_list"     =>
					[
						0 => "+352",
					],
			],
		"mo" =>
			[
				"name"                  => "Macau",
				"flag_emoji_short_name" => ":flag-mo:",
				"phone_prefix_list"     =>
					[
						0 => "+853",
					],
			],
		"mk" =>
			[
				"name"                  => "North Macedonia",
				"flag_emoji_short_name" => ":flag-mk:",
				"phone_prefix_list"     =>
					[
						0 => "+389",
					],
			],
		"mg" =>
			[
				"name"                  => "Madagascar",
				"flag_emoji_short_name" => ":flag-mg:",
				"phone_prefix_list"     =>
					[
						0 => "+261",
					],
			],
		"mw" =>
			[
				"name"                  => "Malawi",
				"flag_emoji_short_name" => ":flag-mw:",
				"phone_prefix_list"     =>
					[
						0 => "+265",
					],
			],
		"my" =>
			[
				"name"                  => "Malaysia",
				"flag_emoji_short_name" => ":flag-my:",
				"phone_prefix_list"     =>
					[
						0 => "+60",
					],
			],
		"mv" =>
			[
				"name"                  => "Maldives",
				"flag_emoji_short_name" => ":flag-mv:",
				"phone_prefix_list"     =>
					[
						0 => "+960",
					],
			],
		"ml" =>
			[
				"name"                  => "Mali",
				"flag_emoji_short_name" => ":flag-ml:",
				"phone_prefix_list"     =>
					[
						0 => "+223",
					],
			],
		"mt" =>
			[
				"name"                  => "Malta",
				"flag_emoji_short_name" => ":flag-mt:",
				"phone_prefix_list"     =>
					[
						0 => "+356",
					],
			],
		"mh" =>
			[
				"name"                  => "Marshall Islands",
				"flag_emoji_short_name" => ":flag-mh:",
				"phone_prefix_list"     =>
					[
						0 => "+692",
					],
			],
		"mr" =>
			[
				"name"                  => "Mauritania",
				"flag_emoji_short_name" => ":flag-mr:",
				"phone_prefix_list"     =>
					[
						0 => "+222",
					],
			],
		"mu" =>
			[
				"name"                  => "Mauritius",
				"flag_emoji_short_name" => ":flag-mu:",
				"phone_prefix_list"     =>
					[
						0 => "+230",
					],
			],
		"yt" =>
			[
				"name"                  => "Mayotte",
				"flag_emoji_short_name" => ":flag-yt:",
				"phone_prefix_list"     =>
					[
						0 => "+262",
					],
			],
		"mx" =>
			[
				"name"                  => "Mexico",
				"flag_emoji_short_name" => ":flag-mx:",
				"phone_prefix_list"     =>
					[
						0 => "+52",
					],
			],
		"fm" =>
			[
				"name"                  => "Micronesia",
				"flag_emoji_short_name" => ":flag-fm:",
				"phone_prefix_list"     =>
					[
						0 => "+691",
					],
			],
		"md" =>
			[
				"name"                  => "Moldova",
				"flag_emoji_short_name" => ":flag-md:",
				"phone_prefix_list"     =>
					[
						0 => "+373",
					],
			],
		"mc" =>
			[
				"name"                  => "Monaco",
				"flag_emoji_short_name" => ":flag-mc:",
				"phone_prefix_list"     =>
					[
						0 => "+377",
					],
			],
		"mn" =>
			[
				"name"                  => "Mongolia",
				"flag_emoji_short_name" => ":flag-mn:",
				"phone_prefix_list"     =>
					[
						0 => "+976",
					],
			],
		"me" =>
			[
				"name"                  => "Montenegro",
				"flag_emoji_short_name" => ":flag-me:",
				"phone_prefix_list"     =>
					[
						0 => "+382",
					],
			],
		"ms" =>
			[
				"name"                  => "Montserrat",
				"flag_emoji_short_name" => ":flag-ms:",
				"phone_prefix_list"     =>
					[
						0 => "+1664",
					],
			],
		"ma" =>
			[
				"name"                  => "Morocco",
				"flag_emoji_short_name" => ":flag-ma:",
				"phone_prefix_list"     =>
					[
						0 => "+212",
					],
			],
		"mz" =>
			[
				"name"                  => "Mozambique",
				"flag_emoji_short_name" => ":flag-mz:",
				"phone_prefix_list"     =>
					[
						0 => "+258",
					],
			],
		"mm" =>
			[
				"name"                  => "Myanmar",
				"flag_emoji_short_name" => ":flag-mm:",
				"phone_prefix_list"     =>
					[
						0 => "+95",
					],
			],
		"na" =>
			[
				"name"                  => "Namibia",
				"flag_emoji_short_name" => ":flag-na:",
				"phone_prefix_list"     =>
					[
						0 => "+264",
					],
			],
		"nr" =>
			[
				"name"                  => "Nauru",
				"flag_emoji_short_name" => ":flag-nr:",
				"phone_prefix_list"     =>
					[
						0 => "+674",
					],
			],
		"np" =>
			[
				"name"                  => "Nepal",
				"flag_emoji_short_name" => ":flag-np:",
				"phone_prefix_list"     =>
					[
						0 => "+977",
					],
			],
		"nl" =>
			[
				"name"                  => "Netherlands",
				"flag_emoji_short_name" => ":flag-nl:",
				"phone_prefix_list"     =>
					[
						0 => "+31",
					],
			],
		"an" =>
			[
				"name"                  => "Netherlands Antilles",
				"flag_emoji_short_name" => ":flag-an:",
				"phone_prefix_list"     =>
					[
						0 => "+599",
					],
			],
		"nc" =>
			[
				"name"                  => "New Caledonia",
				"flag_emoji_short_name" => ":flag-nc:",
				"phone_prefix_list"     =>
					[
						0 => "+687",
					],
			],
		"nz" =>
			[
				"name"                  => "New Zealand",
				"flag_emoji_short_name" => ":flag-nz:",
				"phone_prefix_list"     =>
					[
						0 => "+64",
					],
			],
		"ni" =>
			[
				"name"                  => "Nicaragua",
				"flag_emoji_short_name" => ":flag-ni:",
				"phone_prefix_list"     =>
					[
						0 => "+505",
					],
			],
		"ne" =>
			[
				"name"                  => "Niger",
				"flag_emoji_short_name" => ":flag-ne:",
				"phone_prefix_list"     =>
					[
						0 => "+227",
					],
			],
		"ng" =>
			[
				"name"                  => "Nigeria",
				"flag_emoji_short_name" => ":flag-ng:",
				"phone_prefix_list"     =>
					[
						0 => "+234",
					],
			],
		"nu" =>
			[
				"name"                  => "Niue",
				"flag_emoji_short_name" => ":flag-nu:",
				"phone_prefix_list"     =>
					[
						0 => "+683",
					],
			],
		"kp" =>
			[
				"name"                  => "North Korea",
				"flag_emoji_short_name" => ":flag-kp:",
				"phone_prefix_list"     =>
					[
						0 => "+850",
					],
			],
		"mp" =>
			[
				"name"                  => "Northern Mariana Islands",
				"flag_emoji_short_name" => ":flag-mp:",
				"phone_prefix_list"     =>
					[
						0 => "+1670",
					],
			],
		"no" =>
			[
				"name"                  => "Norway",
				"flag_emoji_short_name" => ":flag-no:",
				"phone_prefix_list"     =>
					[
						0 => "+47",
					],
			],
		"om" =>
			[
				"name"                  => "Oman",
				"flag_emoji_short_name" => ":flag-om:",
				"phone_prefix_list"     =>
					[
						0 => "+968",
					],
			],
		"pk" =>
			[
				"name"                  => "Pakistan",
				"flag_emoji_short_name" => ":flag-pk:",
				"phone_prefix_list"     =>
					[
						0 => "+92",
					],
			],
		"pw" =>
			[
				"name"                  => "Palau",
				"flag_emoji_short_name" => ":flag-pw:",
				"phone_prefix_list"     =>
					[
						0 => "+680",
					],
			],
		"ps" =>
			[
				"name"                  => "Palestine",
				"flag_emoji_short_name" => ":flag-ps:",
				"phone_prefix_list"     =>
					[
						0 => "+970",
					],
			],
		"pa" =>
			[
				"name"                  => "Panama",
				"flag_emoji_short_name" => ":flag-pa:",
				"phone_prefix_list"     =>
					[
						0 => "+507",
					],
			],
		"pg" =>
			[
				"name"                  => "Papua New Guinea",
				"flag_emoji_short_name" => ":flag-pg:",
				"phone_prefix_list"     =>
					[
						0 => "+675",
					],
			],
		"py" =>
			[
				"name"                  => "Paraguay",
				"flag_emoji_short_name" => ":flag-py:",
				"phone_prefix_list"     =>
					[
						0 => "+595",
					],
			],
		"pe" =>
			[
				"name"                  => "Peru",
				"flag_emoji_short_name" => ":flag-pe:",
				"phone_prefix_list"     =>
					[
						0 => "+51",
					],
			],
		"ph" =>
			[
				"name"                  => "Philippines",
				"flag_emoji_short_name" => ":flag-ph:",
				"phone_prefix_list"     =>
					[
						0 => "+63",
					],
			],
		"pn" =>
			[
				"name"                  => "Pitcairn",
				"flag_emoji_short_name" => ":flag-pn:",
				"phone_prefix_list"     =>
					[
						0 => "+64",
					],
			],
		"pl" =>
			[
				"name"                  => "Poland",
				"flag_emoji_short_name" => ":flag-pl:",
				"phone_prefix_list"     =>
					[
						0 => "+48",
					],
			],
		"pt" =>
			[
				"name"                  => "Portugal",
				"flag_emoji_short_name" => ":flag-pt:",
				"phone_prefix_list"     =>
					[
						0 => "+351",
					],
			],
		"pr" =>
			[
				"name"                  => "Puerto Rico",
				"flag_emoji_short_name" => ":flag-pr:",
				"phone_prefix_list"     =>
					[
						0 => "+1787",
						1 => "+1939",
					],
			],
		"qa" =>
			[
				"name"                  => "Qatar",
				"flag_emoji_short_name" => ":flag-qa:",
				"phone_prefix_list"     =>
					[
						0 => "+974",
					],
			],
		"cg" =>
			[
				"name"                  => "Republic of the Congo",
				"flag_emoji_short_name" => ":flag-cg:",
				"phone_prefix_list"     =>
					[
						0 => "+242",
					],
			],
		"re" =>
			[
				"name"                  => "Reunion",
				"flag_emoji_short_name" => ":flag-re:",
				"phone_prefix_list"     =>
					[
						0 => "+262",
					],
			],
		"ro" =>
			[
				"name"                  => "Romania",
				"flag_emoji_short_name" => ":flag-ro:",
				"phone_prefix_list"     =>
					[
						0 => "+40",
					],
			],
		"ru" =>
			[
				"name"                  => "Russia",
				"flag_emoji_short_name" => ":flag-ru:",
				"phone_prefix_list"     =>
					[
						0 => "+79",
					],
			],
		"rw" =>
			[
				"name"                  => "Rwanda",
				"flag_emoji_short_name" => ":flag-rw:",
				"phone_prefix_list"     =>
					[
						0 => "+250",
					],
			],
		"bl" =>
			[
				"name"                  => "Saint Barthelemy",
				"flag_emoji_short_name" => ":flag-bl:",
				"phone_prefix_list"     =>
					[
						0 => "+590",
					],
			],
		"sh" =>
			[
				"name"                  => "Saint Helena",
				"flag_emoji_short_name" => ":flag-sh:",
				"phone_prefix_list"     =>
					[
						0 => "+290",
					],
			],
		"kn" =>
			[
				"name"                  => "Saint Kitts and Nevis",
				"flag_emoji_short_name" => ":flag-kn:",
				"phone_prefix_list"     =>
					[
						0 => "+1869",
					],
			],
		"lc" =>
			[
				"name"                  => "Saint Lucia",
				"flag_emoji_short_name" => ":flag-lc:",
				"phone_prefix_list"     =>
					[
						0 => "+1758",
					],
			],
		"mf" =>
			[
				"name"                  => "Saint Martin",
				"flag_emoji_short_name" => ":flag-mf:",
				"phone_prefix_list"     =>
					[
						0 => "+590",
					],
			],
		"pm" =>
			[
				"name"                  => "Saint Pierre and Miquelon",
				"flag_emoji_short_name" => ":flag-pm:",
				"phone_prefix_list"     =>
					[
						0 => "+508",
					],
			],
		"vc" =>
			[
				"name"                  => "Saint Vincent and the Grenadines",
				"flag_emoji_short_name" => ":flag-vc:",
				"phone_prefix_list"     =>
					[
						0 => "+1784",
					],
			],
		"ws" =>
			[
				"name"                  => "Samoa",
				"flag_emoji_short_name" => ":flag-ws:",
				"phone_prefix_list"     =>
					[
						0 => "+685",
					],
			],
		"sm" =>
			[
				"name"                  => "San Marino",
				"flag_emoji_short_name" => ":flag-sm:",
				"phone_prefix_list"     =>
					[
						0 => "+378",
					],
			],
		"st" =>
			[
				"name"                  => "Sao Tome and Principe",
				"flag_emoji_short_name" => ":flag-st:",
				"phone_prefix_list"     =>
					[
						0 => "+239",
					],
			],
		"sa" =>
			[
				"name"                  => "Saudi Arabia",
				"flag_emoji_short_name" => ":flag-sa:",
				"phone_prefix_list"     =>
					[
						0 => "+966",
					],
			],
		"sn" =>
			[
				"name"                  => "Senegal",
				"flag_emoji_short_name" => ":flag-sn:",
				"phone_prefix_list"     =>
					[
						0 => "+221",
					],
			],
		"rs" =>
			[
				"name"                  => "Serbia",
				"flag_emoji_short_name" => ":flag-rs:",
				"phone_prefix_list"     =>
					[
						0 => "+381",
					],
			],
		"sc" =>
			[
				"name"                  => "Seychelles",
				"flag_emoji_short_name" => ":flag-sc:",
				"phone_prefix_list"     =>
					[
						0 => "+248",
					],
			],
		"sl" =>
			[
				"name"                  => "Sierra Leone",
				"flag_emoji_short_name" => ":flag-sl:",
				"phone_prefix_list"     =>
					[
						0 => "+232",
					],
			],
		"sg" =>
			[
				"name"                  => "Singapore",
				"flag_emoji_short_name" => ":flag-sg:",
				"phone_prefix_list"     =>
					[
						0 => "+65",
					],
			],
		"sx" =>
			[
				"name"                  => "Sint Maarten",
				"flag_emoji_short_name" => ":flag-sx:",
				"phone_prefix_list"     =>
					[
						0 => "+1721",
					],
			],
		"sk" =>
			[
				"name"                  => "Slovakia",
				"flag_emoji_short_name" => ":flag-sk:",
				"phone_prefix_list"     =>
					[
						0 => "+421",
					],
			],
		"si" =>
			[
				"name"                  => "Slovenia",
				"flag_emoji_short_name" => ":flag-si:",
				"phone_prefix_list"     =>
					[
						0 => "+386",
					],
			],
		"sb" =>
			[
				"name"                  => "Solomon Islands",
				"flag_emoji_short_name" => ":flag-sb:",
				"phone_prefix_list"     =>
					[
						0 => "+677",
					],
			],
		"so" =>
			[
				"name"                  => "Somalia",
				"flag_emoji_short_name" => ":flag-so:",
				"phone_prefix_list"     =>
					[
						0 => "+252",
					],
			],
		"za" =>
			[
				"name"                  => "South Africa",
				"flag_emoji_short_name" => ":flag-za:",
				"phone_prefix_list"     =>
					[
						0 => "+27",
					],
			],
		"kr" =>
			[
				"name"                  => "South Korea",
				"flag_emoji_short_name" => ":flag-kr:",
				"phone_prefix_list"     =>
					[
						0 => "+82",
					],
			],
		"ss" =>
			[
				"name"                  => "Южный Судан",
				"flag_emoji_short_name" => ":flag-ss:",
				"phone_prefix_list"     =>
					[
						0 => "+211",
					],
			],
		"es" =>
			[
				"name"                  => "Spain",
				"flag_emoji_short_name" => ":flag-es:",
				"phone_prefix_list"     =>
					[
						0 => "+34",
					],
			],
		"lk" =>
			[
				"name"                  => "Sri Lanka",
				"flag_emoji_short_name" => ":flag-lk:",
				"phone_prefix_list"     =>
					[
						0 => "+94",
					],
			],
		"sd" =>
			[
				"name"                  => "Sudan",
				"flag_emoji_short_name" => ":flag-sd:",
				"phone_prefix_list"     =>
					[
						0 => "+249",
					],
			],
		"sr" =>
			[
				"name"                  => "Suriname",
				"flag_emoji_short_name" => ":flag-sr:",
				"phone_prefix_list"     =>
					[
						0 => "+597",
					],
			],
		"sj" =>
			[
				"name"                  => "Svalbard and Jan Mayen",
				"flag_emoji_short_name" => ":flag-sj:",
				"phone_prefix_list"     =>
					[
						0 => "+47",
					],
			],
		"sz" =>
			[
				"name"                  => "Swaziland",
				"flag_emoji_short_name" => ":flag-sz:",
				"phone_prefix_list"     =>
					[
						0 => "+268",
					],
			],
		"se" =>
			[
				"name"                  => "Sweden",
				"flag_emoji_short_name" => ":flag-se:",
				"phone_prefix_list"     =>
					[
						0 => "+46",
					],
			],
		"ch" =>
			[
				"name"                  => "Switzerland",
				"flag_emoji_short_name" => ":flag-ch:",
				"phone_prefix_list"     =>
					[
						0 => "+41",
					],
			],
		"sy" =>
			[
				"name"                  => "Syria",
				"flag_emoji_short_name" => ":flag-sy:",
				"phone_prefix_list"     =>
					[
						0 => "+963",
					],
			],
		"tw" =>
			[
				"name"                  => "Taiwan",
				"flag_emoji_short_name" => ":flag-tw:",
				"phone_prefix_list"     =>
					[
						0 => "+886",
					],
			],
		"tj" =>
			[
				"name"                  => "Tajikistan",
				"flag_emoji_short_name" => ":flag-tj:",
				"phone_prefix_list"     =>
					[
						0 => "+992",
					],
			],
		"tz" =>
			[
				"name"                  => "Tanzania",
				"flag_emoji_short_name" => ":flag-tz:",
				"phone_prefix_list"     =>
					[
						0 => "+255",
					],
			],
		"th" =>
			[
				"name"                  => "Thailand",
				"flag_emoji_short_name" => ":flag-th:",
				"phone_prefix_list"     =>
					[
						0 => "+66",
					],
			],
		"tg" =>
			[
				"name"                  => "Togo",
				"flag_emoji_short_name" => ":flag-tg:",
				"phone_prefix_list"     =>
					[
						0 => "+228",
					],
			],
		"tk" =>
			[
				"name"                  => "Tokelau",
				"flag_emoji_short_name" => ":flag-tk:",
				"phone_prefix_list"     =>
					[
						0 => "+690",
					],
			],
		"to" =>
			[
				"name"                  => "Tonga",
				"flag_emoji_short_name" => ":flag-to:",
				"phone_prefix_list"     =>
					[
						0 => "+676",
					],
			],
		"tt" =>
			[
				"name"                  => "Trinidad and Tobago",
				"flag_emoji_short_name" => ":flag-tt:",
				"phone_prefix_list"     =>
					[
						0 => "+1868",
					],
			],
		"tn" =>
			[
				"name"                  => "Tunisia",
				"flag_emoji_short_name" => ":flag-tn:",
				"phone_prefix_list"     =>
					[
						0 => "+216",
					],
			],
		"tr" =>
			[
				"name"                  => "Turkey",
				"flag_emoji_short_name" => ":flag-tr:",
				"phone_prefix_list"     =>
					[
						0 => "+90",
					],
			],
		"tm" =>
			[
				"name"                  => "Turkmenistan",
				"flag_emoji_short_name" => ":flag-tm:",
				"phone_prefix_list"     =>
					[
						0 => "+993",
					],
			],
		"tc" =>
			[
				"name"                  => "Turks and Caicos Islands",
				"flag_emoji_short_name" => ":flag-tc:",
				"phone_prefix_list"     =>
					[
						0 => "+1649",
					],
			],
		"tv" =>
			[
				"name"                  => "Tuvalu",
				"flag_emoji_short_name" => ":flag-tv:",
				"phone_prefix_list"     =>
					[
						0 => "+688",
					],
			],
		"vi" =>
			[
				"name"                  => "U.S. Virgin Islands",
				"flag_emoji_short_name" => ":flag-vi:",
				"phone_prefix_list"     =>
					[
						0 => "+1340",
					],
			],
		"ug" =>
			[
				"name"                  => "Uganda",
				"flag_emoji_short_name" => ":flag-ug:",
				"phone_prefix_list"     =>
					[
						0 => "+256",
					],
			],
		"ua" =>
			[
				"name"                  => "Ukraine",
				"flag_emoji_short_name" => ":flag-ua:",
				"phone_prefix_list"     =>
					[
						0 => "+380",
					],
			],
		"ae" =>
			[
				"name"                  => "United Arab Emirates",
				"flag_emoji_short_name" => ":flag-ae:",
				"phone_prefix_list"     =>
					[
						0 => "+971",
					],
			],
		"gb" =>
			[
				"name"                  => "United Kingdom",
				"flag_emoji_short_name" => ":flag-gb:",
				"phone_prefix_list"     =>
					[
						0 => "+44",
					],
			],
		"us" =>
			[
				"name"                  => "United States",
				"flag_emoji_short_name" => ":flag-us:",
				"phone_prefix_list"     =>
					[
						0 => "+1",
					],
			],
		"uy" =>
			[
				"name"                  => "Uruguay",
				"flag_emoji_short_name" => ":flag-uy:",
				"phone_prefix_list"     =>
					[
						0 => "+598",
					],
			],
		"uz" =>
			[
				"name"                  => "Uzbekistan",
				"flag_emoji_short_name" => ":flag-uz:",
				"phone_prefix_list"     =>
					[
						0 => "+998",
					],
			],
		"vu" =>
			[
				"name"                  => "Vanuatu",
				"flag_emoji_short_name" => ":flag-vu:",
				"phone_prefix_list"     =>
					[
						0 => "+678",
					],
			],
		"va" =>
			[
				"name"                  => "Vatican",
				"flag_emoji_short_name" => ":flag-va:",
				"phone_prefix_list"     =>
					[
						0 => "+379",
					],
			],
		"ve" =>
			[
				"name"                  => "Venezuela",
				"flag_emoji_short_name" => ":flag-ve:",
				"phone_prefix_list"     =>
					[
						0 => "+58",
					],
			],
		"vn" =>
			[
				"name"                  => "Vietnam",
				"flag_emoji_short_name" => ":flag-vn:",
				"phone_prefix_list"     =>
					[
						0 => "+84",
					],
			],
		"wf" =>
			[
				"name"                  => "Wallis and Futuna",
				"flag_emoji_short_name" => ":flag-wf:",
				"phone_prefix_list"     =>
					[
						0 => "+681",
					],
			],
		"eh" =>
			[
				"name"                  => "Western Sahara",
				"flag_emoji_short_name" => ":flag-eh:",
				"phone_prefix_list"     =>
					[
						0 => "+212",
					],
			],
		"ye" =>
			[
				"name"                  => "Yemen",
				"flag_emoji_short_name" => ":flag-ye:",
				"phone_prefix_list"     =>
					[
						0 => "+967",
					],
			],
		"zm" =>
			[
				"name"                  => "Zambia",
				"flag_emoji_short_name" => ":flag-zm:",
				"phone_prefix_list"     =>
					[
						0 => "+260",
					],
			],
		"zw" =>
			[
				"name"                  => "Zimbabwe",
				"flag_emoji_short_name" => ":flag-zw:",
				"phone_prefix_list"     =>
					[
						0 => "+263",
					],
			],
	];

	/**
	 * Вернуть конфиг
	 *
	 * @return array
	 */
	public static function load():array {

		return self::_COUNTRY_LIST;
	}

	/**
	 * Вернуть информацию о стране по коду
	 *
	 * @param string $country_code
	 *
	 * @return ConfigItem
	 * @throws CountryNotFound
	 */
	public static function get(string $country_code):ConfigItem {

		if (!isset(self::_COUNTRY_LIST[$country_code])) {
			throw new CountryNotFound("cant find country");
		}

		return ConfigItem::fromArray($country_code, self::_COUNTRY_LIST[$country_code]);
	}
}