<template>
<div id="flex">
	<header><!-- Nothing is here --></header>
	<main>
		<h1>🌏 Domain Checker 🚀</h1>
		<form @submit.prevent="search">
			<div class="form-group">
				<span id="settings-handle">
					<a @click.prevent.stop="toggleSettings()" v-if="!showSettings">Settings</a>
					<a @click.prevent.stop="toggleSettings()" v-else>Close settings</a>
				</span>
				<span id="regex-handle" v-if="showSettings">
					<label><input type="checkbox" :checked="settingsRegexEnabled" v-model="settingsRegexEnabled">
						<span v-if="settingsRegexEnabled">regex <span class="enabled">enabled</span></span>
						<span v-else>regex disabled</span>
					</label>
				</span>
				<input 
					type="text" 
					id="search" 
					:placeholder="showSettings ? placeholders.settings : placeholders.search" 
					v-model="query"
					@keydown="filter"
					@input="filter"
					@paste="filter"
					ref="query"
				>
				<a class="del" href="" v-if="query.length" @click.prevent.stop="query=''">✕</a>
			</div>
		</form>	
		<div id="settings" v-if="showSettings" :class="{'has-items':tlds.length > 2}">
			<div id="settings-wrapper">
				<a v-for="(tld,index) in tlds" :key="index" :class="{'item':true,'checked':checkTLD(tld)}" @click.prevent.stop="toggleTLD(tld)">
					<span>{{punycode(tld)}}</span>
					<span>
						<input type="checkbox" :checked="checkTLD(tld)">
					</span>
				</a>
			</div>
		</div>
		<div class="list" id="results" v-if="!showSettings && query">
			<template v-if="query.indexOf('.') !== -1">
				<a :href="getLink()" target="_blank" :class="{'item':true,'success':result[query],'fail':!result[query]}" role="link">
					<div>{{query}}</div>
					<div v-if="!loading">
						<span class="label available" v-if="check()" target="_blank">Buy</span>
						<span class="label btn busy" v-if="false === check()" target="_blank">Whois</span>
					</div>
					<div v-else>
						<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
					</div>
				</a>
			</template>
			<a :href="getLink(domain)" target="_blank" v-else :class="{'item':true,'success':check(domain),'fail':false === check(domain)}" role="link" v-for="domain in root_domains" :key="domain">
				<div>{{getFullDomain(domain)}}</div>
				<div v-if="!loading">
					<span class="label available" v-if="check(domain)" target="_blank">Buy</span>
					<span class="label btn busy" v-if="false === check(domain)" target="_blank">Whois</span>
				</div>
				<div v-else>
					<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
				</div>
			</a>
		</div>
		<pre>
		</pre>
	</main>
	<footer>
		<a class="github-button" href="https://github.com/codemotion/domain-checker" aria-label="Star codemotion/domain-checker on GitHub">Github</a>
		<!-- <a href="https://ifmo.su"><img src="/images/codex-logo.svg" alt="Codex Team"></a> --> &copy; 2018</footer>
</div>
</template>
<script lang="coffee">
import topLevelDomains from '../data/tld.js'
import punycode from 'punycode'
import domainRegex from 'domain-regex'
export default
	name: 'search'
	data: ->
		query: ''
		root_domains: [
			'ru'
			'su'
			'net'
			'com'
			'org'
			'tech'
			'io'
			'pro'
		],
		topLevelDomains: topLevelDomains
		placeholders: {
			"search": "Type to search…",
			"settings": "Type to filter…"
		}
		result: {}
		timer: null
		loading: false
		showSettings: false
		settingsRegexEnabled: false
	computed: 
		tlds: ->
			@topLevelDomains.filter (tld)=> 
				tld = punycode.toUnicode(tld)
				if @settingsRegexEnabled 
					regex = new RegExp("#{@query}")
					regex.test(tld)
				else if @query.length > 1
					tld == @query
				else
					true
			.sort @sortTlds
	created: ->
		if localStorage && root_domains = JSON.parse(localStorage.getItem('rootDomains'))
			@root_domains = root_domains
	mounted: ->
		window.addEventListener 'keydown', (e)=>
			@query='' if e.key == 'Escape'
		@$refs.query.focus()
	methods:
		punycode: (domain)-> punycode.toUnicode(domain)
		getFullDomain: (domain)-> if @query.indexOf('.') == -1 then [@query,punycode.toUnicode(domain)].join('.') else @query
		check: (domain)->	
			if domain 
				@result[@getFullDomain(domain)]
			else 
				@result[@query]
		getLink: (domain) -> 
			domain = if domain then @getFullDomain(domain) else @query
			if @result[domain] == true
				"https://www.reg.ru/choose/domain/?domains=#{domain}" 
			else 
				"https://www.reg.ru/whois/?dname=#{domain}"
			
		toggleSettings: ->
			@query = ''
			@showSettings = !@showSettings
		checkTLD: (tld) -> @root_domains.indexOf(tld) != -1
		toggleTLD: (tld) ->
			if @root_domains.indexOf(tld) != -1
				@root_domains = @root_domains.filter((domain)=> domain != tld)
			else
				@root_domains.push(tld)
				@root_domains = @root_domains.sort()
			@topLevelDomains.sort @sortTlds
			@$forceUpdate()
			localStorage.setItem('rootDomains',JSON.stringify(@root_domains)) if localStorage
		sortTlds: (a,b)->
			if @root_domains.indexOf(a) != -1
				-1
			else 
				1
		search: ->
			if (domainRegex().test(punycode.toASCII(@query)) || @root_domains.length) && !(@showSettings || @loading)
				clearTimeout(@timer) if @timer
				if @query.length >= 2
					@timer = setTimeout ()=>
						@result = {}
						@loading = true
						axios.get '/api/whois',
							params:
								query: @query
								domains: @root_domains.map((domain)=>@punycode(domain)).join(',')
						.then ({data}) =>
							@result = data
							@loading = false
						.catch (response) =>
							@loading = false
					, 300
		filter: (e)->
			unless @showSettings
				key = e.key || e.data
				e.preventDefault() unless key?.match /[\.\wа-яА-Я_-]{1,1}/ 
			@query = @query.toLowerCase().replace("(.+)\.(\w)")
			@search()

</script>
<style lang="stylus" scoped>
$success = #50ae54
$fail = #ff0024
.enabled
	color $success
.disabled
	color $fail
#flex
	display flex
	flex-direction column
	align-items center
	justify-content space-between
	width 100vw
	height 100vh
	@media(max-width 768px)
		font-size .8rem
	@media(max-width 400px)
		font-size .9rem
a
	text-decoration none
	color rgba(0,0,0,.5)
	cursor pointer !important
h1
	text-align center
	font-weight 300
.form-group
	position relative
#settings-handle
	position absolute
	top -20px
	right 0px
#regex-handle
	position absolute
	top -20px
	left 0px
	a
		color blue
		cursor pointer
	input
		cursor pointer
a.del
	position absolute
	right 15px
	top 12px
	font-size 1.8rem
	color rgba(0,0,0,.1)
	transition transform .2s ease-in
	@media(max-width 768px)
		font-size 1rem
		top 10px
		right 10px
	&:hover
		color rgba(0,0,0,.2)
		transform rotate(-90deg)	
input#search
	width 20em
	font-size 2em
	outline 0 !important
	padding .25em
	border-radius 2px
	text-transform lowercase
	font-family: 'Nunito', sans-serif;
	border 1px solid rgba(0,0,0,.1)
	@media(max-width 768px)
		max-width 100% 
		width 95% !important
		font-size 1.5em
.item
	&:hover
		background-color rgba(0,0,0,.02)
	padding 15px 10px
	border 1px solid rgba(0,0,0,.1)
	display flex
	justify-content space-between
	align-items center
	&:not(:last-child)
		border-bottom none !important
	&:first-child
		border-top none !important
	&.fail
		> div:first-child
			color $fail
	&.success
		> div:first-child
			color $success
a.item.checked
	font-weight bold
.label
	background-color #CCC
	padding 5px 10px
	color #FFF
	border-radius 5px
	text-decoration none
	&.available
		background-color $success
	&.busy
		background-color $fail
#settings
	position relative
	&-wrapper
		max-height 300px
		overflow-y auto
		position relative
	&.has-items::before
		content ''
		display block
		position absolute
		bottom 0px
		left 0
		width 100%
		height 20px
		background linear-gradient(to bottom, rgba(255,255,255,0),rgba(255,255,255,1))
		z-index 100
footer
	padding 15px 0
	display flex
	flex-direction column
	align-items center
	justify-content center
	img
		max-height 50px
		margin 0 0 5px 0
.lds-ellipsis {
  display: inline-block;
  position: relative;
  width: 64px;
  height: 12px;
}
.lds-ellipsis div {
  position: absolute;
  top: 3px;
  width: 11px;
  height: 11px;
  border-radius: 50%;
  background: rgba(0,0,0,.1);
  animation-timing-function: cubic-bezier(0, 1, 1, 0);
}
.lds-ellipsis div:nth-child(1) {
  left: 6px;
  animation: lds-ellipsis1 0.6s infinite;
}
.lds-ellipsis div:nth-child(2) {
  left: 6px;
  animation: lds-ellipsis2 0.6s infinite;
}
.lds-ellipsis div:nth-child(3) {
  left: 26px;
  animation: lds-ellipsis2 0.6s infinite;
}
.lds-ellipsis div:nth-child(4) {
  left: 45px;
  animation: lds-ellipsis3 0.6s infinite;
}
@keyframes lds-ellipsis1 {
  0% {
    transform: scale(0);
  }
  100% {
    transform: scale(1);
  }
}
@keyframes lds-ellipsis3 {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(0);
  }
}
@keyframes lds-ellipsis2 {
  0% {
    transform: translate(0, 0);
  }
  100% {
    transform: translate(19px, 0);
  }
}


</style>
