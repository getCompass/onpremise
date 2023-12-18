module.exports = {
	plugins: {
		"@pandacss/dev/postcss"            : {},
		"@csstools/postcss-cascade-layers" : {},
		"postcss-transform-shortcut"       : {},
		"autoprefixer"                     : {
			overrideBrowserslist: ["Safari >= 13"]
		}
	},
};