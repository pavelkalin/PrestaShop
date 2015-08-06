{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<h3>{l s='Module settings' mod='getresponse'}</h3>
<div class="content">
<span id="formAnchor"></span>
<div class="content-in">
<div class="c-top">
	<div class="mf">
		<div class="c-left simple">
			<h4 class="fontHelveticaNeueLight">
				{l s='If you already are a GetResponse customer, please' mod='getresponse'}
				<b>{l s='enter your API' mod='getresponse'}</b>
				{l s='key to enable the module and' mod='getresponse'}
				<b>{l s='click "Save"' mod='getresponse'}</b>
				{l s=' to continue...' mod='getresponse'}
			</h4>
			<form accept-charset="utf-8" class="FormsEffectLc FormsValidateLc" action="{$action_url|escape:'htmlall':'UTF-8'}" method="post">
				<fieldset>
					<ul>
						<li class="fieldLine clearfix">
							<label class="txtLabel">API key:</label>
							<div class="formElem txtField">
								<div class="fS-Bd">
									<input autocomplete="off" title="API key" id="apiKey" name="api_key" class="jsFE-Input FE-Input" value="{$api_key|escape:'htmlall':'UTF-8'}">
								</div>
							</div>
							<div class="formElem txtInfo">
								<a class="gr-tooltip">
									<span class="gr-tip">
										<h5>{l s='API key' mod='getresponse'}</h5>
										<p>
											{l s='Enter your API key. You can find it on your GetResponse profile in Account Details -> GetResponse API.' mod='getresponse'}
										</p>
									</span>
								</a>
							</div>
							<span class="formErrorIco"></span>
						</li>
					</ul>
					<div class="clearer"></div>
					<div class="submit">
						<input type="submit" value="{l s='Save' mod='getresponse'}" name="ApiConfiguration" class="fontGetresponsePro">
					</div>
				</fieldset>
			</form>
		</div>
		<div class="c-right">
			<h4 class="fontHelveticaNeueLight">
				{l s='If you’re new to GetResponse, please ' mod='getresponse'}
				<b>{l s='click' mod='getresponse'}</b><br><b>{l s='the link below' mod='getresponse'}</b>
				{l s='to check out how GetResponse email marketing tools can boost your business and Prestashop conversions.' mod='getresponse'}
			</h4>
			<ul>
				<li><i class="sprite s-Ico01"></i>{l s='Email marketing returns $41' mod='getresponse'}<br/>{l s='for every $1 spent.' mod='getresponse'}</li>
				<li><i class="sprite s-Ico02"></i>{l s='60%% of shoppers are more apt to buy after receiving an email.' mod='getresponse'}</li>
				<li><i class="sprite s-Ico03"></i>{l s='78%% of marketers confirm the effectiveness of email.' mod='getresponse'}</li>
				<li><i class="sprite s-Ico04"></i>{l s='Email marketing is 20 times more cost-effective than direct mail.' mod='getresponse'}</li>
			</ul>
		</div>
		<div class="clearer"></div>
	</div>
	<h1 class="fontHelveticaNeueLight"><span>{l s='Still not convinced?' mod='getresponse'}</span>
		{l s='Here are 10 reasons' mod='getresponse'}<br>{l s='to do Email Marketing with GetResponse.' mod='getresponse'}
	</h1>
</div>
<div class="c-middle">
	<div class="reasons">
		<div class="bx bx-02">
			<img src="../modules/getresponse/img/01img_v2.jpg" alt="">
			<div class="group">
				<em class="fontGetresponsePro">1</em>
				<h4>{l s='Responsive Email Design' mod='getresponse'}</h4>
			</div>
			<div class="bxHover">
				<h5>{l s='Responsive Email Design' mod='getresponse'}</h5>
				<p>{l s='Emails that adjust automatically to the screen on any device.' mod='getresponse'}</p>
				<div class="btnWrap">
					<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-blue bt-small-x" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=41501">{l s='Learn more' mod='getresponse'}</a>
				</div>
			</div>
		</div>
		<div class="bx bx-03">
			<img src="../modules/getresponse/img/02img_v2.jpg" alt="">
			<div class="group">
				<em class="fontGetresponsePro">2</em>
				<h4>{l s='Landing Page Creator' mod='getresponse'}</h4>
			</div>
			<div class="bxHover">
				<h5>{l s='Landing Page Creator' mod='getresponse'}</h5>
				<p>{l s='Create stunning landing pages in minutes.' mod='getresponse'}</p>
				<div class="btnWrap">
					<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-blue bt-small-x" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=41601">{l s='Learn more' mod='getresponse'}</a>
				</div>
			</div>
		</div>
		<div class="bx bx-03">
			<img src="../modules/getresponse/img/03img_v2.jpg" alt="">
			<div class="group">
				<em class="fontGetresponsePro">3</em>
				<h4>{l s='500+ pre-designed templates' mod='getresponse'}</h4>
			</div>
			<div class="bxHover">
				<h5>{l s='500+ pre-designed templates' mod='getresponse'}</h5>
				<p>{l s='Professionally designed templates will make your messages stand out in every email inbox.' mod='getresponse'}</p>
				<div class="btnWrap">
					<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-blue bt-small-x" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=41701">{l s='Learn more' mod='getresponse'}</a>
				</div>
			</div>
		</div>
		<div class="bx bx-05 bx-rss">
			<img src="../modules/getresponse/img/04img_v2.jpg" alt="">
			<div class="group">
				<em class="fontGetresponsePro">4</em>
				<h4>{l s='Single Opt-in List Import' mod='getresponse'}</h4>
			</div>
			<div class="bxHover">
				<h5>{l s='Single Opt-in List Import' mod='getresponse'}</h5>
				<p>{l s='Quick and simple single opt-in contact list uploads.' mod='getresponse'}</p>
				<div class="btnWrap">
					<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-blue bt-small-x" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=41801">{l s='Learn more' mod='getresponse'}</a>
				</div>
			</div>
		</div>
		<div class="bx bx-05">
			<img src="../modules/getresponse/img/05img.jpg" alt="">
			<div class="group">
				<em class="fontGetresponsePro">5</em>
				<h4>{l s='One-Click Inbox Preview' mod='getresponse'}</h4>
			</div>
			<div class="bxHover">
				<h5>{l s='One-Click Inbox Preview' mod='getresponse'}</h5>
				<p>{l s='Simple rendering test: one click - many previews, includes mobile devices.' mod='getresponse'}</p>
				<div class="btnWrap">
					<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-blue bt-small-x" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=41901">{l s='Learn more' mod='getresponse'}</a>
				</div>
			</div>
		</div>
		<div class="bx bx-04">
			<img src="../modules/getresponse/img/06img_v2.png" alt="">
			<div class="group">
				<em class="fontGetresponsePro">6</em>
				<h4>{l s='Marketing Automation' mod='getresponse'}</h4>
			</div>
			<div class="bxHover">
				<h5>{l s='Marketing Automation' mod='getresponse'}</h5>
				<p>{l s='Time-based and action-based messages.' mod='getresponse'}</p>
				<div class="btnWrap">
					<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-blue bt-small-x" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=42001">{l s='Learn more' mod='getresponse'}</a>
				</div>
			</div>
		</div>
		<div class="bx bx-05">
			<img src="../modules/getresponse/img/07img_v2.png" alt="">
			<div class="group">
				<em class="fontGetresponsePro">7</em>
				<h4>{l s='Mobile Apps' mod='getresponse'}</h4>
			</div>
			<div class="bxHover">
				<h5>{l s='Mobile Apps' mod='getresponse'}</h5>
				<p>{l s='Email marketing on the go, for your iPhone and Android' mod='getresponse'}</p>
				<div class="btnWrap">
					<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-blue bt-small-x" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=42101">{l s='Learn more' mod='getresponse'}</a>
				</div>
			</div>
		</div>
		<div class="bx bx-05 bx-deliver">
			<img src="../modules/getresponse/img/08img.png" alt="">
			<div class="group">
				<em class="fontGetresponsePro">8</em>
				<h4>{l s='Verified Deliverability' mod='getresponse'}</h4>
			</div>
			<div class="bxHover">
				<h5>{l s='Verified Deliverability' mod='getresponse'}</h5>
				<p>{l s='Up to 99.5%% deliverability with solutions to keep your emails out of the junk box.' mod='getresponse'}</p>
				<div class="btnWrap">
					<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-blue bt-small-x" title="Try It Free" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=42201">{l s='Learn more' mod='getresponse'}</a>
				</div>
			</div>
		</div>
		<div class="bx bx-03">
			<img src="../modules/getresponse/img/09img_v2.png" alt="">
			<div class="group">
				<em class="fontGetresponsePro">9</em>
				<h4>{l s='Email Analytics' mod='getresponse'}</h4>
			</div>
			<div class="bxHover">
				<h5>{l s='Email Analytics' mod='getresponse'}</h5>
				<p>{l s='Insightful, easy-to-read, all-inclusive graphs and charts.' mod='getresponse'}</p>
				<div class="btnWrap">
					<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-blue bt-small-x" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=42301">{l s='Learn more' mod='getresponse'}</a>
				</div>
			</div>
		</div>
		<div class="bx bx-03 bx-istock">
			<img src="../modules/getresponse/img/10img.jpg" alt="">
			<div class="group">
				<em class="fontGetresponsePro">10</em>
				<h4>{l s='1000+ Free iStockphoto images' mod='getresponse'}</h4>
			</div>
			<div class="bxHover">
				<h5>{l s='1000+ Free iStockphoto images' mod='getresponse'}</h5>
				<p>{l s="Multiple themes, simple drag'n'drop edition inside newsletter." mod='getresponse'}</p>
				<div class="btnWrap">
					<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-blue bt-small-x" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=42401">{l s='Learn more' mod='getresponse'}</a>
				</div>
			</div>
		</div>
		<div class="clearer"></div>
	</div>
</div>
<div class="c-bottom">
	<h2 class="fontHelveticaNeueLight">{l s='World’s Easiest Email Marketing.' mod='getresponse'}</h2>
	<div class="callTa">
		<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="scroll bt bt-green bt-large-x fontGetresponsePro" title="{l s='Start Free Trial' mod='getresponse'}" target="_blank">{l s='Start Free Trial' mod='getresponse'}</a>
	</div>
	<div class="tos">
		<p>{l s='30-day free trial. No credit card required.' mod='getresponse'}</p>
	</div>
</div>
</div>
</div>
