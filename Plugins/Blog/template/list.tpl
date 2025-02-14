{% if mode === "list_empty" %}
	<p>{{Lang.galerie.no-item-found}}</p>
{% else %}
	{% for k, v in news %}
		<article>
			{% if runPlugin.getConfigVal("hideContent") == false %}
				<header>
					{% if pluginsManager.isActivePlugin("galerie") && galerie.searchByFileName(v.img) %}
						<img class="featured" src="{{v.imgUrl}}" alt="{{v.img}}"/>
					{% endif %}
					<div class="item-head">
						<h2>
							<a href="{{v.url}}">{{v.name}}</a>
						</h2>
						<p class="date">{{v.date}}
							{% if runPlugin.getConfigVal("comments") && v.commentsOff == false %}
								|
								<a href="{{v.url}}#comments">{{ newsManager.countComments(v.id) }}
								commentaire{% if newsManager.countComments(v.id) > 1 %}s{% endif %}</a>
							{% endif %}
							 | <span class="item-categories"><i class="fa-regular fa-folder-open"></i>
							{% if count(v.cats) == 0 %}
								Non classé
							{% else %}
								{% for cat in v.cats %}
									<span class="blog-label-category"><a href="{{ cat.url }}">{{ cat.label }}</a></span>
								{% endfor %}
							{% endif %}
						</p>
                    </span>
					</div>
				</header>
				{% if v.intro %}
					{{htmlspecialchars_decode(v.intro)}}
				{% else %}
					{{htmlspecialchars_decode(v.content)}}
				{% endif %}
			{% else %}
				<h2>
					<a href="{{v.url}}">{{v.name}}</a>
				</h2>
				<p class="date">{{v.date}}
					{% if runPlugin.getConfigVal("comments") && v.commentsOff == false %}
						|
						{{ newsManager.countComments(v.id) }}
						commentaire{% if newsManager.countComments(v.id) > 1 %}s{% endif %}
					{% endif %}
				</p>
			{% endif %}
		</article>
	{% endfor %}
	{% if pagination %}
		<ul class="pagination">
			{% for k, v in pagination %}
				<li>
					<a href="{{v.url}}">{{v.num}}</a>
				</li>
			{% endfor %}
		</ul>
	{% endif %}
{% endif %}
