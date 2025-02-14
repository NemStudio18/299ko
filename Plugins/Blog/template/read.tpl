<article>
	<header>
		{% if pluginsManager.isActivePlugin("galerie") && galerie.searchByFileName(item.getImg) %}
			<img class="featured" src="{{ item.getImgUrl }}" alt="{{ item.getName }}"/>
		{% endif %}
		<div class="item-head">
			<p class="date">
			{{ Lang.blog.posted-date(item.getReadableDate())}}
				{% if runPlugin.getConfigVal("comments") && item.getCommentsOff == false %}
					|
					<a href="#comments">
					{% if newsManager.countComments() == 0 %}
						{{ Lang.blog.comments.none-comments }}
					{% elseif newsManager.countComments() == 1 %}
						{{ Lang.blog.comments-one-comment }}
					{% else %}
						{{ Lang.blog.comments-nb-comments(newsManager.countComments()) }}
					{% endif %}
					</a> | 
				{% endif %}
				{% if count(categories) == 0 %}
					{{ Lang.blog.categories.none}}
				{% else %}
					dans
					{% for cat in categories %}
						<span class="blog-label-category"><a href="{{ cat.url }}">{{ cat.label }}</a></span> 
					{% endfor %}
				{% endif %}
				| <a href="{{ runPlugin.getPublicUrl }}">{{ Lang.blog.back-to-list }}</a>
			</p>
		</div>
		
	</header>
	{{ TOC }}
	{{ generatedHtml }}
	{% if runPlugin.getConfigVal("displayAuthor") %}
		<footer>
			<div class='blog-author'>
				<div class='blog-avatar'>
					<img src='{{runPlugin.getConfigVal("authorAvatar")}}' alt='{{runPlugin.getConfigVal("authorName")}}'/>
				</div>
				<div class='blog-infos'>
					<div class='blog-infos-name'>
						<span>{{runPlugin.getConfigVal("authorName")}}</span>
					</div>
					<div class='blog-infos-bio'>
						{{htmlspecialchars_decode(runPlugin.getConfigVal("authorBio"))}}
					</div>
				</div>
			</div>
		</footer>
	{% endif %}
</article>
{% if runPlugin.getConfigVal("comments") && item.getCommentsOff == false %}
	<section id="comments">
		<header>
			<div class="item-head">
				<h2>{{ Lang.blog-comments }}</h2>
			</div>
		</header>
		{% if newsManager.countComments(item.getId) == 0 %}
			<p>{{ Lang.blog.there-is-no-comment }}</p>
		{% else %}
			<ul class="comments-list">
				{% for k, v in newsManager.getComments() %}
					{{ v. show }}
				{% endfor %}
			</ul>
		{% endif %}
		<div id="comments-add-container">
			<div id="comments-add-respond">
				<h2 id="comments-title" data-title="{{ Lang.blog.comments.add-comment}}">{{ Lang.blog.comments.add-comment}}</h2>
				<form method="post" action="{{ commentSendUrl }}">
					<button id="comments-cancel-respond" class="small" title="{{Lang.blog.comments.cancel-response}}" aria-label="{{Lang.blog.comments.cancel-response}}"><i class="fa-solid fa-xmark"></i></button>
					<input type="hidden" name="id" value="{{item.getId}}"/>
					<input type="hidden" name="commentParentId" id="commentParentId" value="0"/>
					<input type="hidden" name="back" value="{{item.getUrl}}"/>
					<p>
						<label for="author">{{ Lang.blog.comments-name }}</label><br>
						<input style="display:none;" type="text" name="_author" value=""/>
						<input type="text" name="author" id="author" required="required"/>
					</p>
					<p>
						<label for="authorEmail">{{ Lang.blog.comments-mail }}</label><br><input type="email" name="authorEmail" id="authorEmail" required="required"/></p>
					<p>
					<p>
						<label for="authorWebsite">{{ Lang.blog.comments-website }}</label><br><input type="url" name="authorWebsite" id="authorWebsite"/></p>
					<p>
						<label for="commentContent">{{ Lang.blog.comments-content }}</label>
						<textarea name="commentContent" id="commentContent" required="required"></textarea>
					</p>
					{% if antispam %}
						{{antispamField}}
					{% endif %}
					<p><input type="submit" value="{{ Lang.contact.form_send }}"/></p>
				</form>
			</div>
		</div>
	</section>
{% endif %}