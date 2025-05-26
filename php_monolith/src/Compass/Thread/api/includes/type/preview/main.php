<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use Compass\Conversation\Domain_Search_Entity_Preview_Task_AttachToThreadMessage;
use JetBrains\PhpStorm\Pure;

/**
 * класс для работы с сущностью UrlPreview
 */
class Type_Preview_Main {

	// исключаемсые первые октеты для ссылок типа ip
	protected const _EXCLUDED_FIRST_OCTET_LIST = [
		10, 127, 192, 0,
	];

	// список доступных протоколов
	protected const _ALLOWED_PROTOCOL_LIST = [
		"http://", "https://",
	];

	// список символов на которые не могут начинаться ссылки
	protected const _NOT_ALLOWED_FOR_START_LINK_CHAR_LIST = [
		"?"  => "?",
		"["  => "[",
		"]"  => "]",
		"("  => "(",
		")"  => ")",
		":"  => ":",
		","  => ",",
		"_"  => "_",
		"~"  => "~",
		"*"  => "*",
		"\"" => "\"",
		"+"  => "+",
		"-"  => "-",
		"`"  => "`",
	];

	// список символов на которые не могут оканчиваться ссылки
	protected const _NOT_ALLOWED_FOR_END_LINK_CHAR_LIST = [
		"?"  => "?",
		"["  => "[",
		"]"  => "]",
		"("  => "(",
		")"  => ")",
		":"  => ":",
		","  => ",",
		"~"  => "~",
		"\"" => "\"",
		"+"  => "+",
		"-"  => "-",
		"`"  => "`",
		"."  => ".",
	];

	protected const _FORMATTING_CHARACTERS_LIST = [
		"*"  => "*",
		"_"  => "_",
		"~"  => "~",
		"``" => "``",
		"++" => "++",
		"--" => "--",
	];

	// список символов на которые могут заканчиваться ссылки - в единичном экземпляре
	protected const _ALLOWED_END_LINK_CHAR_LIST = [
		"*" => "*",
		"_" => "_",
	];

	// спецсимволы для конструкций вида \"спецсимвол\"|\"строка\"|\"строка\"]
	protected const _SPECIAL_CHARS = [
		"@",
		"#",
	];

	// список всех доменов верхнего уровня http://data.iana.org/TLD/tlds-alpha-by-domain.txt
	protected const _TOP_DOMAIN_LIST = [
		"aaa", "barclays", "cba", "degree", "xn--11b4c3d", "xn--1ck2e1b", "xn--1qqw23a", "xn--2scrj9c", "xn--30rr7y", "xn--3bst00m", "xn--3ds443g", "xn--3e0b707e", "xn--tckwe", "xn--tiq49xqyj", "xn--unup4y", "xn--vermgensberater-ctb", "xn--vermgensberatung-pwb", "xn--vhquv", "xn--vuq861b", "xn--w4r85el8fhu5dnra", "xn--w4rs40l", "xn--wgbh1c", "xn--wgbl6a", "xn--xhq521b", "xn--xkc2al3hye2a", "xn--xkc2dl3a5ee0h", "xn--y9a3aq", "xn--yfro4i67o", "xn--ygbi2ammx", "xn--zfr164b", "xn--3hcrj9c", "xn--3oq18vl8pn36a", "xn--3pxu8k", "xn--42c2d9a", "xn--45br5cyl", "xn--45brj9c", "xn--45q11c", "xn--4gbrim", "xn--54b7fta0cc", "xn--55qw42g", "xn--55qx5d", "xn--5su34j936bgsg", "xn--5tzm5g", "xn--6frz82g", "xn--6qq986b3xl", "xn--80adxhks", "xn--80ao21a", "xn--80aqecdr1a", "xn--80asehdb", "xn--80aswg", "xn--8y0a063a", "xn--90a3ac", "xn--90ae", "xn--90ais", "xn--9dbq2a", "xn--9et52u", "xn--9krt00a", "xn--b4w605ferd", "xn--bck1b9a5dre4c", "xn--c1avg", "xn--c2br7g", "xn--cck2b3b", "xn--cg4bki", "xn--clchc0ea0b2g2a9gcd", "xn--czr694b", "xn--czrs0t", "xn--czru2d", "xn--d1acj3b", "xn--d1alf", "xn--e1a4c", "xn--eckvdtc9d", "xn--efvy88h", "xn--fct429k", "xn--fhbei", "xn--fiq228c5hs", "xn--fiq64b", "xn--fiqs8s", "xn--fiqz9s", "xn--fjq720a", "xn--flw351e", "xn--fpcrj9c3d", "xn--fzc2c9e2c", "xn--fzys8d69uvgm", "xn--g2xx48c", "xn--gckr3f0f", "xn--gecrj9c", "xn--gk3at1e", "xn--h2breg3eve", "xn--h2brj9c", "xn--h2brj9c8c", "xn--hxt814e", "xn--i1b6b1a6a2e", "xn--imr513n", "xn--io0a7i", "xn--j1aef", "xn--j1amh", "xn--j6w193g", "xn--jlq61u9w7b", "xn--jvr189m", "xn--kcrx77d1x4a", "xn--kprw13d", "xn--kpry57d", "xn--kpu716f", "xn--kput3i", "xn--l1acc", "xn--lgbbat1ad8j", "xn--mgb9awbf", "xn--mgba3a3ejt", "xn--mgba3a4f16a", "xn--mgba7c0bbn0a", "xn--mgbaakc7dvf", "xn--mgbaam7a8h", "xn--mgbab2bd", "xn--mgbah1a3hjkrd", "zw", "aarp", "abarth", "abb", "abbott", "abbvie", "abc", "able", "abogado", "abudhabi", "ac", "academy", "accenture", "accountant", "accountants", "aco", "actor", "ad", "adac", "ads", "adult", "ae", "aeg", "aero", "aetna", "af", "afamilycompany", "afl", "africa", "ag", "agakhan", "agency", "ai", "aig", "aigo", "airbus",
		"airforce", "airtel", "akdn", "al", "alfaromeo", "alibaba", "alipay", "allfinanz", "allstate", "ally", "alsace", "alstom", "am", "americanexpress", "americanfamily", "avianca", "aw", "aws", "ax", "axa", "az", "azure", "ba", "baby", "baidu", "banamex", "bananarepublic", "band", "bank", "bar", "barcelona", "barclaycard", "buy", "buzz", "bv", "bw", "by", "bz", "bzh", "ca", "cab", "cafe", "cal", "call", "calvinklein", "cam", "camera", "camp", "cancerresearch", "canon", "capetown", "country", "coupon", "coupons", "courses", "cpa", "cr", "credit", "creditcard", "creditunion", "cricket", "crown", "crs", "cruise", "cruises", "csc", "cu", "expert", "exposed", "express", "extraspace", "fage", "fail", "fairwinds", "faith", "family", "fan", "fans", "farm", "farmers", "fashion", "fast", "fedex", "institute", "insurance", "insure", "int", "intel", "international", "intuit", "investments", "io", "ipiranga", "iq", "ir", "irish", "is", "ismaili", "ist", "xn--mgbai9azgqp6j", "xn--mgbayh7gpa", "xn--mgbbh1a", "xn--mgbbh1a71e", "xn--mgbc0a9azcg", "xn--mgbca7dzdo", "xn--mgbcpq6gpa1a", "xn--mgberp4a5d4ar", "xn--mgbgu82a", "xn--mgbi4ecexp", "xn--mgbpl2fh", "xn--mgbt3dhd", "xn--mgbtx2b", "xn--mgbx4cd0ab", "xn--mix891f", "xn--mk1bu44c", "xn--mxtq1m", "xn--ngbc5azd", "xn--ngbe9e0a", "xn--ngbrx", "xn--node", "xn--nqv7f", "xn--nqv7fs00ema", "xn--nyqy26a", "xn--o3cw4h", "xn--ogbpf8fl", "xn--otu796d", "xn--p1acf", "xn--p1ai", "xn--pbt977c", "xn--pgbs0dh", "xn--pssy2u", "xn--q7ce6a", "xn--q9jyb4c", "xn--qcka1pmc", "xn--qxa6a", "xn--qxam", "xn--rhqv96g", "xn--rovu88b", "xn--rvc1e0am3e", "xn--s9brj9c", "xn--ses554g", "xn--t60b56a", "amex", "amfam", "amica", "amsterdam", "analytics", "android", "anquan", "anz", "ao", "aol", "apartments", "app", "apple", "aq", "aquarelle", "ar", "arab", "aramco", "archi", "army", "arpa", "art", "arte", "as", "asda", "asia", "associates", "at", "athleta", "attorney", "au", "auction", "audi", "audible", "audio", "auspost", "author", "auto", "autos", "barclays", "barefoot", "bargains", "baseball", "basketball", "bauhaus", "bayern", "bb", "bbc", "bbt", "bbva", "bcg", "bcn", "bd", "be", "beats", "beauty", "beer", "bentley", "berlin", "best", "bestbuy", "bet", "bf", "bg", "bh", "bharti", "bi", "bible", "bid", "bike", "bing", "bingo", "bio", "biz", "bj", "black", "blackfriday", "blockbuster", "blog", "bloomberg", "blue", "bm", "bms", "bmw", "bn", "bnpparibas", "bo", "boats", "boehringer", "bofa", "bom", "bond", "boo", "book", "booking", "bosch", "bostik", "boston", "bot", "boutique", "box", "br", "bradesco", "bridgestone", "broadway", "broker", "brother", "brussels", "bs", "bt", "budapest", "bugatti", "build", "builders", "business",
		"capital", "capitalone", "car", "caravan", "cards", "care", "career", "careers", "cars", "casa", "case", "caseih", "cash", "casino", "cat", "catering", "catholic", "cba", "cbn", "cbre", "cbs", "cc", "cd", "ceb", "center", "ceo", "cern", "cf", "cfa", "cfd", "cg", "ch", "chanel", "channel", "charity", "chase", "chat", "cheap", "chintai", "christmas", "chrome", "church", "ci", "cipriani", "circle", "cisco", "citadel", "citi", "citic", "city", "cityeats", "ck", "cl", "claims", "cleaning", "click", "clinic", "clinique", "clothing", "cloud", "club", "clubmed", "cm", "cn", "co", "coach", "codes", "coffee", "college", "cologne", "com", "comcast", "commbank", "community", "company", "compare", "computer", "comsec", "condos", "construction", "consulting", "contact", "contractors", "cooking", "cookingchannel", "cool", "coop", "corsica", "cuisinella", "cv", "cw", "cx", "cy", "cymru", "cyou", "cz", "dabur", "dad", "dance", "data", "date", "dating", "datsun", "day", "dclk", "dds", "de", "deal", "dealer", "deals", "degree", "delivery", "dell", "deloitte", "delta", "democrat", "dental", "dentist", "desi", "design", "dev", "dhl", "diamonds", "diet", "digital", "direct", "directory", "discount", "discover", "dish", "diy", "dj", "dk", "dm", "dnp", "do", "docs", "doctor", "dog", "domains", "dot", "download", "drive", "dtv", "dubai", "duck", "dunlop", "dupont", "durban", "dvag", "dvr", "dz", "earth", "eat", "ec", "eco", "edeka", "edu", "education", "ee", "eg", "email", "emerck", "energy", "engineer", "engineering", "enterprises", "epson", "equipment", "er", "ericsson", "erni", "es", "esq", "estate", "esurance", "et", "etisalat", "eu", "eurovision", "eus", "events", "exchange", "feedback", "ferrari", "ferrero", "fi", "fiat", "fidelity", "fido", "film", "final", "finance", "financial", "fire", "firestone", "firmdale", "fish", "fishing", "fit", "fitness", "fj", "fk", "flickr", "flights", "flir", "florist", "flowers", "fly", "fm", "fo", "foo", "food", "foodnetwork", "football", "ford", "forex", "forsale", "forum", "foundation", "fox", "fr", "free", "fresenius", "frl", "frogans", "frontdoor", "frontier", "ftr", "fujitsu", "fujixerox", "fun", "fund", "furniture", "futbol", "fyi", "ga", "gal", "gallery", "gallo", "gallup", "game", "games", "gap", "garden", "gb", "gbiz", "gd", "gdn", "ge", "gea", "gent", "genting", "george", "gf", "gg", "ggee", "gh", "gi", "gift", "gifts", "gives", "giving", "gl", "glade", "glass", "gle", "global", "globo", "gm", "gmail", "gmbh", "gmo", "gmx", "gn", "godaddy", "gold", "goldpoint", "golf", "goo", "goodyear", "goog", "google", "gop", "got", "gov", "gp", "gq", "gr", "grainger", "graphics", "gratis", "green", "gripe", "grocery", "group", "gs", "gt", "gu", "guardian", "gucci", "guge", "guide", "guitars", "guru", "gw", "gy", "hair", "hamburg", "hangout", "haus", "hbo",
		"hdfc", "hdfcbank", "health", "healthcare", "help", "helsinki", "here", "hermes", "hgtv", "hiphop", "hisamitsu", "hitachi", "hiv", "hk", "hkt", "hm", "hn", "hockey", "holdings", "holiday", "homedepot", "homegoods", "homes", "homesense", "honda", "horse", "hospital", "host", "hosting", "hot", "hoteles", "hotels", "hotmail", "house", "how", "hr", "hsbc", "ht", "hu", "hughes", "hyatt", "hyundai", "ibm", "icbc", "ice", "icu", "id", "ie", "ieee", "ifm", "ikano", "il", "im", "imamat", "imdb", "immo", "immobilien", "in", "inc", "industries", "infiniti", "info", "ing", "ink", "istanbul", "it", "itau", "itv", "iveco", "jaguar", "java", "jcb", "jcp", "je", "jeep", "jetzt", "jewelry", "jio", "jll", "jm", "jmp", "jnj", "jo", "jobs", "joburg", "jot", "joy", "jp", "jpmorgan", "jprs", "juegos", "juniper", "kaufen", "kddi", "ke", "kerryhotels", "kerrylogistics", "kerryproperties", "kfh", "kg", "kh", "ki", "kia", "kim", "kinder", "kindle", "kitchen", "kiwi", "km", "kn", "koeln", "komatsu", "kosher", "kp", "kpmg", "kpn", "kr", "krd", "kred", "kuokgroup", "kw", "ky", "kyoto", "kz", "la", "lacaixa", "lamborghini", "lamer", "lancaster", "lancia", "land", "landrover", "lanxess", "lasalle", "lat", "latino", "latrobe", "law", "lawyer", "lb", "lc", "lds", "lease", "leclerc", "lefrak", "legal", "lego", "lexus", "lgbt", "li", "lidl", "life", "lifeinsurance", "lifestyle", "lighting", "like", "lilly", "limited", "limo", "lincoln", "linde", "link", "lipsy", "live", "living", "lixil", "lk", "llc", "llp", "loan", "loans", "locker", "locus", "loft", "lol", "london", "lotte", "lotto", "love", "lpl", "lplfinancial", "lr", "ls", "lt", "ltd", "ltda", "lu", "lundbeck", "lupin", "luxe", "luxury", "lv", "ly", "ma", "macys", "madrid", "maif", "maison", "makeup", "man", "management", "mango", "map", "market", "marketing", "markets", "marriott", "marshalls", "maserati", "mattel", "mba", "mc", "mckinsey", "md", "me", "med", "media", "meet", "melbourne", "meme", "mr", "ms", "msd", "mt", "mtn", "mtr", "mu", "museum", "mutual", "mv", "mw", "mx", "my", "mz", "na", "nab", "nagoya", "name", "nationwide", "natura", "navy", "nba", "nc", "memorial", "men", "menu", "merckmsd", "metlife", "mg", "mh", "miami", "microsoft", "mil", "mini", "mint", "mit", "mitsubishi", "mk", "ml", "mlb", "mls", "mm", "mma", "mn", "mo", "mobi", "mobile", "moda", "moe", "moi", "mom", "monash", "money", "monster", "mormon", "mortgage", "moscow", "moto", "motorcycles", "mov", "movie", "mp", "mq", "ne", "nec", "net", "netbank", "netflix", "network", "neustar", "new", "newholland", "news", "next", "nextdirect", "nexus", "nf", "nfl", "ng", "ngo", "nhk", "ni", "nico", "nike", "software", "sohu", "solar", "solutions", "song", "sony", "soy", "space", "sport", "spot", "spreadbetting", "sr", "srl", "ss", "st", "stada", "staples", "star", "statebank", "statefarm", "nikon", "ninja", "nissan", "nissay", "nl", "no", "nokia", "northwesternmutual", "norton", "now", "nowruz", "nowtv", "np", "nr", "nra", "nrw", "ntt", "nu", "nyc", "nz", "obi", "observer", "off", "office", "okinawa", "olayan", "olayangroup", "oldnavy", "ollo", "om", "omega", "one", "ong", "onl", "online", "onyourside", "ooo", "open", "oracle", "orange", "org", "organic", "origins", "osaka", "otsuka", "ott", "ovh", "pa", "page", "panasonic", "paris", "pars", "partners", "parts", "party", "passagens", "pay", "pccw", "pe", "pet", "pf", "pfizer", "pg", "ph", "pharmacy", "swiftcover", "swiss", "sx", "sy", "sydney", "symantec", "systems", "sz", "tab", "taipei", "talk", "taobao", "target", "tatamotors", "tatar", "tattoo", "tax", "taxi", "tc", "tci", "td", "tdk", "phd", "philips", "phone", "photo", "photography", "photos", "physio", "pics", "pictet", "pictures", "pid", "pin", "ping", "pink", "pioneer", "pizza", "pk", "pl", "place", "play", "playstation", "team", "tech", "technology", "tel", "temasek", "tennis", "teva", "tf", "tg", "th", "thd", "theater", "theatre", "tiaa", "tickets", "tienda", "tiffany", "tips", "tires", "tirol", "tj", "tjmaxx", "plumbing", "plus", "pm", "pn", "pnc", "pohl", "poker", "politie", "porn", "post", "pr", "pramerica", "praxi", "press", "prime", "pro", "prod", "productions", "prof", "progressive", "promo", "properties", "tjx", "tk", "tkmaxx", "tl", "tm", "tmall", "tn", "to", "today", "tokyo", "tools", "top", "toray", "toshiba", "total", "tours", "town", "toyota", "toys", "tr", "trade", "trading", "training", "travel", "property", "protection", "pru", "prudential", "ps", "pt", "pub", "pw", "pwc", "py", "qa", "qpon", "quebec", "quest", "qvc", "racing", "radio", "raid", "re", "read", "realestate", "realtor", "realty", "travelchannel", "travelers", "travelersinsurance", "trust", "trv", "tt", "tube", "tui", "tunes", "tushu", "tv", "tvs", "tw", "tz", "ua", "ubank", "ubs", "ug", "uk", "unicom", "university", "uno", "uol", "recipes", "red", "redstone", "redumbrella", "rehab", "reise", "reisen", "reit", "reliance", "ren", "rent", "rentals", "repair", "report", "republican", "rest", "restaurant", "review", "reviews", "rexroth", "rich", "ups", "us", "uy", "uz", "va", "vacations", "vana", "vanguard", "vc", "ve", "vegas", "ventures", "verisign", "versicherung", "vet", "vg", "vi", "viajes", "video", "vig", "viking", "villas", "vin", "vip", "virgin", "richardli", "ricoh", "rightathome", "ril", "rio", "rip", "rmit", "ro", "rocher", "rocks", "rodeo", "rogers", "room", "rs", "rsvp", "ru", "rugby", "ruhr", "run", "rw", "rwe", "ryukyu", "sa", "saarland", "safe", "safety", "visa", "vision", "viva", "vivo", "vlaanderen", "vn", "vodka", "volkswagen", "volvo", "vote", "voting", "voto", "voyage", "vu", "vuelos", "wales", "walmart", "walter", "wang", "wanggou", "watch", "watches", "weather", "sakura", "sale", "salon", "samsclub", "samsung", "sandvik", "sandvikcoromant", "sanofi", "sap", "sarl", "sas", "save", "saxo", "sb", "sbi", "sbs", "sc", "sca", "scb", "schaeffler", "schmidt", "scholarships", "school", "schule",
		"weatherchannel", "webcam", "weber", "website", "wed", "wedding", "weibo", "weir", "wf", "whoswho", "wien", "wiki", "williamhill", "win", "windows", "wine", "winners", "wme", "wolterskluwer", "woodside", "work", "works", "world", "schwarz", "science", "scjohnson", "scor", "scot", "sd", "se", "search", "seat", "secure", "security", "seek", "select", "sener", "services", "ses", "seven", "sew", "sex", "sexy", "sfr", "sg", "sh", "shangrila", "sharp", "shaw", "shell", "shia", "shiksha", "shoes", "shop", "shopping", "shouji", "show", "showtime", "shriram", "wow", "ws", "wtc", "wtf", "xbox", "xerox", "xfinity", "xihuan", "xin", "कॉम", "セール", "佛山", "ಭಾರತ", "慈善", "集团", "在线", "한국", "ଭାରତ", "大众汽车", "点看", "คอม", "ভাৰত", "ভারত", "八卦", "موقع", "বাংলা", "公益", "公司", "香格里拉", "网站", "移动", "我爱你", "москва", "қаз", "католик", "онлайн", "сайт", "联通", "срб", "бг", "бел", "קום", "时尚", "微博", "淡马锡", "ファッション", "орг", "नेट", "ストア", "삼성", "சிங்கப்பூர்", "商标", "商店", "商城", "дети", "мкд", "ею", "ポイント", "新闻", "家電", "كوم", "中文网", "中信", "中国", "中國", "娱乐", "谷歌", "భారత్", "ලංකා", "電訊盈科", "购物", "クラウド", "ભારત", "通販", "भारतम्", "भारत", "भारोत", "网店", "संगठन", "餐厅", "网络", "укр", "香港", "诺基亚", "食品", "飞利浦", "台湾", "台灣", "手表", "手机", "мон", "الجزائر", "عمان", "ارامكو", "ایران", "العليان", "اتصالات", "امارات", "بازار", "موريتانيا", "پاکستان", "الاردن", "بارت", "بھارت", "المغرب", "ابوظبي", "البحرين", "السعودية", "ڀارت", "كاثوليك", "سودان", "همراه", "عراق", "مليسيا", "澳門", "닷컴", "政府", "شبكة", "بيتك", "عرب", "გე", "机构", "组织机构", "健康", "ไทย", "سورية", "招聘", "рус", "рф", "珠宝", "تونس", "大拿", "ລາວ", "みんな", "グーグル", "ευ", "ελ", "世界", "書籍", "ഭാരതം", "ਭਾਰਤ", "网址", "닷넷", "コム", "天主教", "游戏", "vermögensberater", "vermögensberatung", "企业", "信息", "嘉里大酒店", "嘉里", "مصر", "قطر", "广东", "இலங்கை", "இந்தியா", "հայ", "新加坡", "فلسطين", "政务", "xxx", "xyz", "yachts", "yahoo", "yamaxun", "yandex", "ye", "yodobashi", "yoga", "yokohama", "you", "youtube", "yt", "yun", "za", "zappos", "zara", "zero", "zip", "zm", "zone", "zuerich", "zw", "stc", "stcgroup", "stockholm", "storage", "store", "stream", "studio", "study", "style", "su", "sucks", "supplies", "supply", "support", "surf", "surgery", "suzuki", "sv", "swatch", "si", "silk", "sina", "singles", "site", "sj", "sk", "ski", "skin", "sky", "skype", "sl", "sling", "sm", "smart", "smile", "sn", "sncf", "so", "soccer", "social", "softbank", "test", "local",
	];

	// преобразование числового типа превью в текстовый
	public const PREVIEW_TYPE_SCHEMA = [
		PREVIEW_TYPE_SITE           => "site",
		PREVIEW_TYPE_IMAGE          => "image",
		PREVIEW_TYPE_PROFILE        => "profile",
		PREVIEW_TYPE_CONTENT        => "content",
		PREVIEW_TYPE_RESOURCE       => "resource",
		PREVIEW_TYPE_VIDEO          => "video",
		PREVIEW_TYPE_COMPASS_INVITE => "compass_invite",
		PREVIEW_TYPE_SIMPLE         => "simple",
	];

	// получает запись с url_preview
	public static function get(string $preview_map):array {

		return Gateway_Db_CompanyData_PreviewList::getOne($preview_map);
	}

	// создает запись в url_preview
	public static function create(string $preview_map, array $data = []):void {

		$insert = [
			"preview_hash" => \CompassApp\Pack\Preview::getPreviewHash($preview_map),
			"is_deleted"   => 0,
			"created_at"   => time(),
			"updated_at"   => 0,
			"data"         => $data,
		];

		Gateway_Db_CompanyData_PreviewList::insert($insert);
	}

	// обновляет запись в url_preview
	public static function set(string $preview_map, array $set):void {

		Gateway_Db_CompanyData_PreviewList::set($preview_map, $set);
	}

	// прикрепляет к сообщению список ссылок и map превью
	// @long
	public static function attachToMessage(int   $user_id, string $message_map, string $parent_conversation_map, array $link_list, array $users, string $preview_map = null, int $preview_type = null,
							   array $preview_image = []):int {

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);

		$block_id = self::_tryGetBlockId($thread_map, $message_map);
		if ($block_id === false) {
			return Gateway_Socket_Conversation::getThreadsUpdatedVersion($parent_conversation_map);
		}

		$threads_updated_version = Gateway_Socket_Conversation::updateThreadsUpdatedData($parent_conversation_map);

		/** начало транзакции **/
		Gateway_Db_CompanyThread_Main::beginTransaction();
		$block_row = Gateway_Db_CompanyThread_MessageBlock::getForUpdate($thread_map, $block_id);

		// получаем сообщение из блока
		$message = Type_Thread_Message_Block::getMessage($message_map, $block_row);

		// прикрепляем список ссылок к сообщению
		$message = Type_Thread_Message_Main::getHandler($message)::addLinkList($message, $link_list);

		// если к сообщению нужно прикрепить превью
		if (!is_null($preview_map)) {

			$message = Type_Thread_Message_Main::getHandler($message)::addPreview($message, $preview_map, $preview_type);
			Domain_Search_Entity_Preview_Task_AttachToThreadMessage::queueList([["preview_map" => $preview_map, "message_map" => $message["message_map"]]], array_keys($users));
		}

		// если к сообщению нужно прикрепить информацию по изображению с превью
		if (count($preview_image) > 0) {
			$message = Type_Thread_Message_Main::getHandler($message)::addPreviewImage($message, $preview_image);
		}

		self::_updateBlockRow($thread_map, $block_id, $message_map, $block_row, $message);
		Gateway_Db_CompanyThread_Main::commitTransaction();

		/** конец транзакции **/

		// прикрепляем превью к чату
		if (!is_null($preview_map)) {

			$message_created_at = Type_Thread_Message_Main::getHandler($message)::getMessageCreatedAt($message);
			self::_attachToConversation($user_id, $message_map, $thread_map, $preview_map, $message_created_at, $link_list);
		}

		return $threads_updated_version;
	}

	/**
	 * Прикрепить превью к чату
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 * @param string $thread_map
	 * @param int    $message_created_at
	 * @param string $preview_map
	 *
	 * @return void
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 */
	protected static function _attachToConversation(int $user_id, string $message_map, string $thread_map, string $preview_map, int $message_created_at, array $link_list):void {

		$thread_meta_row = Gateway_Db_CompanyThread_ThreadMeta::getOne($thread_map);

		// получаем  идентификатор родительской сущности
		$parent_message_type = Type_Thread_SourceParentRel::getType($thread_meta_row["parent_rel"]);

		if ($parent_message_type !== PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE) {
			return;
		}

		$conversation_message_map = Type_Thread_ParentRel::getMap($thread_meta_row["parent_rel"]);

		Gateway_Socket_Conversation::attachPreviewToConversation($user_id, $message_map, $conversation_message_map, $preview_map, $message_created_at, $link_list);
	}

	/**
	 * получаем идентификатор блока с сообщением
	 *
	 * @mixed
	 */
	protected static function _tryGetBlockId(string $thread_map, string $message_map):bool|int {

		// получаем dynamic треда
		$dynamic_obj = Type_Thread_Dynamic::get($thread_map);

		// получаем идентификатор блока сообщения
		$block_id = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

		// если блока на существует
		if (!Type_Thread_Message_Block::isExist($dynamic_obj, $block_id)) {
			return false;
		}

		// если блок в архиве
		if (!Type_Thread_Message_Block::isActive($dynamic_obj, $block_id)) {
			return false;
		}

		return $block_id;
	}

	// обновляем блок с сообщением
	protected static function _updateBlockRow(string $thread_map, int $block_id, string $message_map, array $block_row, array $message):void {

		// обновляем блок с сообщением
		$block_row["data"][$message_map] = $message;
		Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);
	}

	// ищем все совпадения
	public static function doFindAllLinks(string $text):array {

		$link_list = [];

		// для самого начала нам нужно найти все слова
		$exploded_arr = preg_split("/[ \r\n]/", $text, -1, PREG_SPLIT_NO_EMPTY);

		// суть алгоритма в том что мы весь текст делим на слова
		// среди этих слов ищем слово где есть что то похожее на домен,
		// если нашли то дальше парсим это как ссылку пытаемся получить протокол и путь ссылки
		foreach ($exploded_arr as $word) {

			$link = self::_getLinkFromWord($word);
			if ($link === false) {
				continue;
			}

			$link_list[] = $link;
		}
		return [
			$link_list,
			count($exploded_arr),
		];
	}

	/**
	 * ищем меншины в тексте и отделяем их пробелом
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function doCheckAllMentions(string $text):string {

		foreach (self::_SPECIAL_CHARS as $special_character) {

			if (!inHtml($text, $special_character)) {
				continue;
			}

			// для начала нужно найти все меншины
			$text_split_ar = explode("[\"" . $special_character . "\"|", $text);

			// суть в том, чтобы в каждой части проверить последний символ
			// если это не пробел - добавить его
			if (count($text_split_ar) > 1) {

				foreach ($text_split_ar as &$text_split_str) {

					if (!str_ends_with($text_split_str, " ")) {
						$text_split_str = $text_split_str . " ";
					}
				}
				$text = implode("[\"" . $special_character . "\"|", $text_split_ar);
			}
		}
		return $text;
	}

	// проверяем что слово является ссылкой
	// @mixed - получаем ссылку из слова
	protected static function _getLinkFromWord(string $word):bool|string {

		// для этого весь текст делим по символам которых не может быть в домене и ходим по каждой части, наша цель найти что то похожее на домен
		$split_arr = preg_split("/[\/?#*_()\[\]&^%$!,<>`~]/", $word, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($split_arr as $v) {

			// получаем домен и порт
			$domain = self::_getDomain($v);
			$port   = self::_getPort($v);
			if (mb_strlen($domain) < 1) {
				continue;
			}
			$pos = mb_strpos($word, $domain);

			// отрезаем все до домена и после домена
			$protocol       = mb_substr($word, 0, $pos);
			$start_link_pos = self::_getStartLinkPos($protocol);
			if ($start_link_pos === false) {
				$start_link_pos = $pos;
			}

			$path         = mb_substr($word, $pos + mb_strlen($domain) + mb_strlen($port));

			$end_link_pos = $pos + mb_strlen($domain) + mb_strlen($port) + self::_getEndLinkPos($path, $word);
			return mb_substr($word, $start_link_pos, $end_link_pos - $start_link_pos);
		}
		return false;
	}

	// получаем реальный домен
	protected static function _getDomain(string $domain):string {

		$domain = self::_prepareDomain($domain);

		// для начало отрезаем порт если он есть и проверяем, что порт это число
		$exploded_list = explode(":", $domain, 2);
		if (count($exploded_list) > 1) {

			if (filter_var($exploded_list[1], FILTER_VALIDATE_INT) === false) {
				return "";
			}
			$domain = $exploded_list[0];
		}

		// если это ip
		preg_match("/^(\d{1,3})\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $domain, $matches);
		if (count($matches) > 0) {
			return $domain;
		}

		// проверяем что последняя часть домена это домен верхнего уровня
		$exploded_list = explode(".", $domain);
		if (count($exploded_list) < 2) {
			return "";
		}

		// если домен имееет в себе @ то это почта и ее мы не парсим
		$bracket_pos = mb_strpos($domain, "@");
		if ($bracket_pos !== false) {
			return "";
		}

		if (in_array(mb_strtolower(end($exploded_list)), self::_TOP_DOMAIN_LIST)) {
			return $domain;
		}

		return "";
	}

	// получаем порт
	#[Pure]
	protected static function _getPort(string $domain):string {

		$domain = self::_prepareDomain($domain);

		// проверяем наличие порта
		$exploded_list = explode(":", $domain, 2);
		if (count($exploded_list) === 1) {
			return "";
		}

		return ":" . $exploded_list[1];
	}

	// подготавливаем домен
	protected static function _prepareDomain(string $link):string {

		$link = trim($link, implode(self::_NOT_ALLOWED_FOR_START_LINK_CHAR_LIST));
		$link = trim($link, implode(self::_NOT_ALLOWED_FOR_END_LINK_CHAR_LIST));

		return $link;
	}

	// проверяем что это протокол
	// @mixed - may be return false
	protected static function _getStartLinkPos(string $protocol):bool|int {

		// если протокол пуст то это ок
		if (mb_strlen($protocol) < 1) {
			return 0;
		}

		foreach (self::_ALLOWED_PROTOCOL_LIST as $v) {

			// если в конце строки протокол то возвращаем позицию протокола
			$pos = mb_strripos($protocol, $v);
			if (mb_strlen($protocol) - $pos == mb_strlen($v)) {
				return $pos;
			}
		}

		return false;
	}

	// получаем позицию на которой ссылка заканчивается
	protected static function _getEndLinkPos(string $path, string $word):int {

		$pos = mb_strlen($path);
		if (mb_strlen($pos) < 1) {
			return 0;
		}

		// первый символ после домена должен быть / проверяем это
		$first_char = mb_substr($path, 0, 1);
		if (!in_array($first_char, ["/", "#", "?"])) {
			return 0;
		}

		// если ссылка заканчивается на опредленные символы то не парсим их
		// получаем последний символ ссылки
		$pos = self::_getLastCharPosFromLink($path, $word);
		return $pos;
	}

	// получам позицию последнего символа в ссылке
	protected static function _getLastCharPosFromLink(string $path, string $word):int {

		$pos = mb_strlen($path);
		if (mb_strlen($path) < 1) {
			return 0;
		}

		// если заканчивается на скобку то ищем все открывающие и закрывающие если закрывающих больше то отрезаем все до нее
		$bracket_pos = mb_strripos($path, ")");
		if ($bracket_pos !== false) {

			if (mb_substr_count($path, ")") > mb_substr_count($path, "(")) {

				$path = mb_substr($path, 0, $bracket_pos);
				return self::_getLastCharPosFromLink($path, $word);
			}

			// если закрывающая скобка последняя в ссылке - больше ничего не делаем
			if ($bracket_pos == $pos - 1) {
				return $pos;
			}
		}

		// получаем предудыщий символ
		$last_char = mb_substr($path, -1);

		return self::_getPositionByLastAllowChar($pos, $last_char, $word, $path);
	}

	// функция для проверки строки на наличие запрещенных символов
	protected static function _getPositionByLastAllowChar(string $pos, string $last_char, string $word, string $path):string {

		// проверяем символ
		if (isset(self::_NOT_ALLOWED_FOR_END_LINK_CHAR_LIST[$last_char]) || isset(self::_NOT_ALLOWED_FOR_END_LINK_CHAR_LIST[mb_substr($path, -1, 2)])) {

			// проверка для двоеточия — не трогаем, если это шортнейм эмодзи
			if ($last_char === ":") {

				// пытаемся найти последний шортнейм вида :smile: на конце строки
				if (preg_match("/:([a-zA-Z0-9_+\-]+):$/u", $path, $matches)) {

					$emoji_candidate = $matches[0];

					// перепроверяем по списку эмодзи
					$emoji_list = array_merge(
						array_values(\BaseFrame\Conf\Emoji::EMOJI_FLAG_LIST),
						array_values(\BaseFrame\Conf\Emoji::EMOJI_LIST),
						array_keys(\BaseFrame\Conf\Emoji::EMOJI_ALIAS_SHORT_NAME_LIST),
					);

					if (in_array($emoji_candidate, $emoji_list, true)) {
						return $pos;
					}
				}
			}

			$path = mb_substr($path, 0, -1);
			$pos  = self::_getLastCharPosFromLink($path, $word);
		}

		// проверяем символы на конце - что им можно быть в конце, но только в единичном экземпляре
		if (isset(self::_ALLOWED_END_LINK_CHAR_LIST[$last_char]) && substr_count($path, $last_char) > 1) {

			$path = mb_substr($path, 0, mb_strlen($path) - 1);
			$pos  = self::_getLastCharPosFromLink($path, $word);
		}

		// проверяем символы на конце - что они доступны, относятся к формитированию - и если их четное число в целом, значит это форматирование
		if (isset(self::_ALLOWED_END_LINK_CHAR_LIST[$last_char]) && (substr_count($word, $last_char) % 2) == 0) {

			$path = mb_substr($path, 0, mb_strlen($path) - 1);
			$pos  = self::_getLastCharPosFromLink($path, $word);
		}

		return $pos;
	}

	// -------------------------------------------------------
	// UTILS METHODS
	// -------------------------------------------------------

	/**
	 * провеяет, что текст это ссылка с форматированием или без.
	 * например, ++yandex.ru++ === yandex.ru.
	 *
	 * @param string $text текст, содержащий ссылку
	 * @param string $url  ссылка без форматирования
	 */
	public static function checkIsTextAreUrl(string $text, string $url):bool {

		// пытаемся экранировать ссылку
		$quoted_url = self::_tryPregQuoteUrl($url);

		// если форматирование валидно и ссылка экранирована
		if (self::_checkIsCorrectLeftRightFormatting($text, $url) && !empty($quoted_url)) {

			// так как форматирование слева и справа равно, берём слева
			$possible_formatting = explode($url, $text)[0];

			// создаём паттерн соответствия строки и валидного форматирования
			$pattern_formatting_find = self::_makePregPatternFormattingFind();

			// если есть соответствие, значит текст это ссылка с форматированием или без
			if (preg_match($pattern_formatting_find, $possible_formatting)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * пытается экранировать строку.
	 * если строка не была экранирована, вернёт экранированную.
	 * если строка уже была экранирована, вернёт её же.
	 *
	 * @param string $url экранированная или не экранированная ссылка
	 *
	 * @return string экранированная ссылка | пустая строка
	 */
	protected static function _tryPregQuoteUrl(string $url):string {

		// экранируем ссылку
		$quoted_url = preg_quote($url, "/");

		// подсчитываем сколько символов было экранировано
		$quoted_symbols_count = mb_strlen($quoted_url) - mb_strlen($url);

		// соответствие в регулярках такое:
		// (регулярка) '\\\'     => '\\' (экранированный слеш),
		// (регулярка) '\\\\\\\' => '\\\\' (два экранированных слеша)
		$pattern_url_not_quoted_before = "/\\\[^\\\]/"; // \.{1}
		$pattern_url_quoted_before     = "/\\\\\\\./"; // \\.{1}

		// не была экранирована до этого
		// количество найденных экранирований равно подсчитанному ранее числу
		if (preg_match_all($pattern_url_not_quoted_before, $quoted_url) == $quoted_symbols_count) {

			return $quoted_url;
			// была экранирована до этого
			// делим на два, так как, если ссылка была экранирована, то каждый слеш также экранируется.
		} elseif (preg_match_all($pattern_url_quoted_before, $quoted_url) == $quoted_symbols_count / 2) {

			return $url;
			// иначе, не валидная ссылка
		} else {
			return "";
		}
	}

	/**
	 * проверяет, что форматирование слева и справа соответствует друг другу.
	 * например, "_*+" === "+*_" или "*+" === "*+".
	 *
	 * @param string $text    текст с форматированием или без
	 * @param string $sub_str подстрока без форматирования
	 */
	protected static function _checkIsCorrectLeftRightFormatting(string $text, string $sub_str):bool {

		$parts = explode($sub_str, $text);

		$left_part  = $parts[0];
		$right_part = $parts[1];

		$preg_exp_for_one_of_formatting_symbols_match = self::_makePregExpForOneOfFormattingSymbolsMatch();

		// ищем символы форматирования в левой и правой части
		$left_part_formatted_symbols_count  = preg_match_all($preg_exp_for_one_of_formatting_symbols_match, $left_part, $left_part_formatted_symbols);
		$left_part_formatted_symbols        = $left_part_formatted_symbols[0] ?? [];
		$right_part_formatted_symbols_count = preg_match_all($preg_exp_for_one_of_formatting_symbols_match, $right_part, $right_part_formatted_symbols);
		$right_part_formatted_symbols       = $right_part_formatted_symbols[0] ?? [];

		// если длина левой и правой части не равны
		if ($left_part_formatted_symbols_count !== $right_part_formatted_symbols_count) {
			return false;
		}

		// если помимо валидных символов форматирования в левой или правой части было что-то еще
		if ((strlen(implode($left_part_formatted_symbols)) != strlen($left_part)) || (strlen(implode($right_part_formatted_symbols)) != strlen($right_part))) {
			return false;
		}

		// если символы форматирования в левой и правой части не совпадают
		for ($index = 0; $index < $left_part_formatted_symbols_count; $index++) {

			if (!in_array($left_part_formatted_symbols[$index], $right_part_formatted_symbols)) {
				return false;
			}
			if (!in_array($right_part_formatted_symbols[$index], $left_part_formatted_symbols)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * формирует регулярное выражение, которое ищет один из символов форматирования
	 */
	protected static function _makePregExpForOneOfFormattingSymbolsMatch():string {

		// получаем список всех символов форматирования
		$formatting_symbol_list = array_values(self::_FORMATTING_CHARACTERS_LIST);
		$formatting_symbol_list = array_map(fn(string $element) => preg_quote($element), $formatting_symbol_list);

		// возвращаем регулярное выражение
		return "/" . implode("|", $formatting_symbol_list) . "/";
	}

	/**
	 * создаёт регулярное выражение, которое проверяет, что строка - это валидная строка форматирования
	 * проверяется одна часть, а не обе: либо левая, либо правая.
	 * например, "_*+word+*_", в данном случае регулярка ориентирована на поиск "_*+" или "+*_".
	 */
	protected static function _makePregPatternFormattingFind():string {

		// получаем список всех символов форматирования
		$formatting_symbol_list = array_values(self::_FORMATTING_CHARACTERS_LIST);

		// получаем список символов форматирования цвета, они имеют длину 2: ++, --, ``
		$formatting_color_symbol_list = array_filter($formatting_symbol_list, fn(string $element) => strlen($element) == 2);

		// получаем список символов форматирования внешнего вида текста, они имеют длину 1: _, ~, *
		$formatting_text_symbol_list = array_filter($formatting_symbol_list, fn(string $element) => strlen($element) == 1);

		// экранируем оба массива символов поэлементно
		$quoted_formatting_color_symbol_list = array_map(fn(string $element) => preg_quote($element), $formatting_color_symbol_list);
		$quoted_formatting_text_symbol_list  = array_map(fn(string $element) => preg_quote($element), $formatting_text_symbol_list);

		// объединяем символы форматирования цвета символом | (ИЛИ) в строку для создания условия "Один или другой" в регулярном выражении
		$imploded_quoted_formatted_color_symbol_list = implode("|", $quoted_formatting_color_symbol_list);

		// добавляем конструкцию, которая гарантирует, что этот символ далее в строке не встречается
		$pattern_part_not_repeat_color_symbol = "(?!.*(" . $imploded_quoted_formatted_color_symbol_list . "))";

		// формируем часть регулярного выражения, которая означает "Только один или ни одного из символов цвета есть в строке"
		$pattern_part_formatting_color_find = "(?:(" . $imploded_quoted_formatted_color_symbol_list . ")" . $pattern_part_not_repeat_color_symbol . ")";

		$pattern_parts_one_of_formatting_text_symbol_list = [];

		// формируется часть регулярного выражения, которая для каждого символа внешнего вида текста означает:
		// "каждый из них может встретиться не более 1 раза"
		foreach ($quoted_formatting_text_symbol_list as $quoted_formatting_text_symbol) {

			$pattern_part = "(?:" . $quoted_formatting_text_symbol . "(?!.*" . $quoted_formatting_text_symbol . "))";

			$pattern_parts_one_of_formatting_text_symbol_list[] = $pattern_part;
		}

		// объединяем сформированные условия для каждого символа в одну строку с помощью символа | (ИЛИ)
		$pattern_one_of_formatting_text_symbol = implode("|", $pattern_parts_one_of_formatting_text_symbol_list);

		// задаем ограничение длины форматирования:
		// в ней может быть не более одного символа цвета и все символы внешнего вида строки.
		$possible_formatting_length = "{0," . count($formatting_text_symbol_list) + 2 . "}";

		// формируем итоговую строку, добаляем символ начала строки
		$pattern_formatting_find = "/^(";

		// объединяем две части, связанные с символами цвета и символами внешнего вида текста символом | (ИЛИ)
		$pattern_formatting_find .= $pattern_one_of_formatting_text_symbol . "|" . $pattern_part_formatting_color_find;

		// добавляем ограничение по длине
		$pattern_formatting_find .= ")" . $possible_formatting_length . "$/";

		return $pattern_formatting_find;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// рекурсивная функция проверки на блокировку
	protected static function _isBlocked(string $domain, array $black_list):bool {

		// заменяем последнюю часть домена на  звездочку
		$temp = explode(".", $domain);
		if (count($temp) < 3) {
			return false;
		}

		// формируем более короткий домен
		$new_domain = $temp[1];
		for ($i = 2; $i < count($temp); $i++) {
			$new_domain .= "." . $temp[$i];
		}

		// ищем домен в списке если есть
		$new_domain_with_mask = "*." . $new_domain;
		if (isset($black_list[$new_domain_with_mask])) {
			return true;
		}

		return self::_isBlocked($new_domain, $black_list);
	}
}
