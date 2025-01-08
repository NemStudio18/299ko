<!doctype html>
<html lang="{{ lang.getLocale }}">
<head>
    {% HOOK.frontHead %}
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="robots" content="noindex"><meta name="googlebot" content="noindex">
    <title>{{ Lang.users-register }}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5" />
    <link rel="icon" href="{{ SHOW.themeIcon }}" />
    {{ SHOW.linkTags }}
    {{ SHOW.scriptTags }}
    {{ SHOW.showMetas }}
    {% HOOK.endFrontHead %}
</head>
<body class="login">
    <div id="alert-msg">
        {{ SHOW.displayMsg }}
    </div>
    <div id="register" class="card">
        <header>
            <div>
                <h2>{{ Lang.users-register }}</h2>
            </div>
        </header>
        <form method="post" action="{{ registerLink }}">
            <p>
                <label for="email">{{ Lang.email }}</label><br>
                <input style="display:none;" type="text" name="_email" value="" autocomplete="off" />
                <input type="email" id="email" name="email" required />
            </p>
            <p>
                <label for="password">{{ Lang.password }}</label>
                <input type="password" id="password" name="password" required />
            </p>
            <p>
                <a class="button alert" href="{{CORE.getConfigVal("siteUrl")}}">{{ Lang.cancel }}</a>
                <input type="submit" class="button" value="{{ Lang.validate }}" />
            </p>
            <p class="just_using">
                <a target="_blank" href="https://github.com/299ko/">{{ Lang.site-just-using() }}</a>
            </p>
        </form>
    </div>
</body>
</html>
