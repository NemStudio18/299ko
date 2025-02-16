<!DOCTYPE html>
<html lang="{{ Common\Lang.getLocale}}">
	<head>
		{% HOOK.frontHead %}
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>{{ Common\Show.titleTag }}</title>
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5"/>
		<meta name="description" content="{{ Common\Show.metaDescriptionTag }}"/>
		<link rel="icon" href="{{ Common\Show.themeIcon }}"/>
		<base href="{{ Common\Show.siteUrl }}/">
		{{ Common\Show.linkTags }}
		{{ Common\Show.scriptTags }}
		{{ Common\Show.showMetas }}
		{% HOOK.endFrontHead %}
	</head>
	<body>
		<div id="container">
			<div id="header">
				<nav id="header_content">
					<button id="mobile_menu" aria-label="{{ Lang.site-menu-label }}"></button>
					<p id="siteName">
						<a href="{{ Common\Show.siteUrl }}">{{ Common\Show.siteName }}</a>
					</p>
					<ul id="navigation">
						{{ Common\Show.mainNavigation }}
						{% HOOK.endMainNavigation %}
					</ul>
				</nav>
			</div>
			<div id="alert-msg">
				{{ Common\Show.displayMsg }}
			</div>
			<div id="banner">
				<div id="siteDesc">
					{{ Common\Show.siteDesc}}
				</div>
			</div>
			<main id="body">
				{% IF CORE.getConfigVal(hideTitles) == 0 %}
					<div id="pageTitle">
						{{ Common\Show.mainTitle }}
					</div>
				{% ENDIF %}
				<div id="body-page">
					<div id="content" class="{{ Common\Show.pluginId }}">
						{{ CONTENT }}
					</div>
					{{ Common\Show.displayPublicSidebar() }}
				</div>
			</main>
			<div id="footer">
				<div id="footer_content">
					{% HOOK.footer %}
					<p>
						<a target='_blank' href='https://299ko.ovh'>{{ Lang.site-just-using( ) }}</a>
						-
						{{ Lang.site-theme }}
						{{ Common\Show.theme }}
						-
						{% if IS_LOGGED %}
							<a rel="nofollow" href="{{ ROUTER.generate("logout") }}">{{ Lang.core-disconnection }}</a>
							{% if IS_ADMIN %}
								-
								<a rel="nofollow" href="{{ ROUTER.generate("admin") }}">{{ Lang.site-admin }}</a>
							{% endif %}
						{% else %}
							<a rel="nofollow" href="{{ ROUTER.generate("login") }}">{{ Lang.core-connection }}</a>
						{% endif %}
					</p>
					{% HOOK.endFooter %}
				</div>
			</div>
		</div>
		{% HOOK.endFrontBody %}
	</body>
</html>
