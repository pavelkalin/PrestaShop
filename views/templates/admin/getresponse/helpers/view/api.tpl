{*
* @author     Grzegorz Struczynski <gstruczynski@implix.com>
* @copyright  GetResponse
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="content">
	<h3>{l s='Connection settings' mod='getresponse'}</h3>
	<span id="formAnchor"></span>
	<section class="api-section">
		<div class="container">
			<div class="row row-flex row-flex-wrap">
				<div class="col-lg-5">
					<div class="api content-in">
						<h3>
							{l s='Already a GetResponse user?' mod='getresponse'}
						</h3>
						<p>{l s='Enter your GetResponse API key to connect. Click' mod='getresponse'} <strong>Save</strong> {l s='to import your forms, campaigns and enable turning customers into subscribers.' mod='getresponse'}</p>
						<form accept-charset="utf-8" class="FormsEffectLc FormsValidateLc" action="{$action_url|escape:'htmlall':'UTF-8'}" method="post">
							<fieldset>
								<div class="fieldLine clearfix">
									<label class="txtLabel">API key</label>
									<div class="input-tip">
										<input autocomplete="off" type="text" title="API key" id="apiKey" name="api_key" class="jsFE-Input FE-Input" value="{$api_key|escape:'htmlall':'UTF-8'}">
										<span>
											<abbr title='{l s='API key' mod='getresponse'}|{l s='You can find your API key in the settings of your GetResponse account. Log in to GetResponse and go to <strong>My account > Account details > API & OAuth</strong> to find the key.' mod='getresponse'}' rel="tooltip"></abbr>
										</span>
									</div>
									<span class="formErrorIco"></span>
								</div>
								<div class="btns">
									<div class="submit">
										<input type="submit" value="{l s='Save' mod='getresponse'}" name="ApiConfiguration" class="fontGetresponsePro">
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
				<div class="col-lg-7">
					<div class="content-in" style="cursor:pointer;" onclick="window.open('http://app.getresponse.com/track_customer.html?tid=42501', '_blank')">
						<h3>
							{l s='New to GetResponse?' mod='getresponse'}
						</h3>
						<p><a href="http://app.getresponse.com/track_customer.html?tid=42501" title="{l s='Start your free trial' mod='getresponse'}" target="_blank"><strong>{l s='Start your free trial' mod='getresponse'}</strong></a> and check out how GetResponse email marketing tools can boost your business and PrestaShop conversions.</p>
						<ul>
							<li>{l s='Email marketing returns $41 for every $1 spent.' mod='getresponse'}</li>
							<li>{l s='60% of shoppers are more apt to buy after receiving an email.' mod='getresponse'}</li>
							<li>{l s='78% of marketers confirm the effectiveness of email.' mod='getresponse'}</li>
							<li>{l s='Email marketing is 20 times more cost-effective than direct mail.' mod='getresponse'}</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<div class="content">
	<h3>{l s='Still not convinced? Here are 10 reasons to do email marketing with GetResponse.' mod='getresponse'}</h3>
	<section class="api-section">
		<div class="container">
			<div class="row">
				<div class="ten-reasons">
						<div class="row row-flex row-flex-wrap gutter-5">
							<div class="col-lg-6">
								<div class="reson r1">
									<h3><i>1</i><span>{l s='Responsive Email Design' mod='getresponse'}</span></h3>
									<div class="img"></div>
									<div class="bxHover">
						                <h5>{l s='Responsive Email Design' mod='getresponse'}</h5>
						                <p>{l s='Emails that adjust automatically to the screen on any device.' mod='getresponse'}</p>
						                <div class="btn-wrap">
						                    <a href="http://app.getresponse.com/track_customer.html?tid=42501" class="bt" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=41501"> {l s='Learn more' mod='getresponse'}</a>
						                </div>
						            </div>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="reasons flex-col">
									<div class="flex-grow">
										<div class="reson r2 flex-col">
											<h3><i>2</i><span>{l s='Landing Page Creator' mod='getresponse'}</span></h3>
											<div class="img flex-col"></div>
											<div class="bxHover">
								                <h5>{l s='Landing Page Creator' mod='getresponse'}</h5>
								                <p>{l s='Create stunning landing pages in minutes.' mod='getresponse'}</p>
								                <div class="btn-wrap">
								                    <a href="http://app.getresponse.com/track_customer.html?tid=42501" class="bt" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=41601"> {l s='Learn more' mod='getresponse'}</a>
								                </div>
								            </div>
										</div>
									</div>
									<div class="flex-grow">
										<div class="reson r3 flex-col">
											<h3><i>3</i><span>{l s='500+ pre-designed templates' mod='getresponse'}</span></h3>
											<div class="img flex-col"></div>
											<div class="bxHover">
											    <h5>{l s='500+ pre-designed templates' mod='getresponse'}</h5>
											    <p>{l s='Professionally designed templates will make your messages stand out in every email inbox.' mod='getresponse'}</p>
											    <div class="btn-wrap">
											        <a href="http://app.getresponse.com/track_customer.html?tid=42501" class="bt" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=41701"> {l s='Learn more' mod='getresponse'}</a>
											    </div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row row-flex row-flex-wrap gutter-5">
							<div class="col-md-3">
								<div class="reson r4 flex-col">
									<h3><i>4</i><span>{l s='Single Opt-in List Import' mod='getresponse'}</span></h3>
									<div class="img flex-grow"></div>
									<div class="bxHover">
									    <h5>{l s='Single Opt-in List Import' mod='getresponse'}</h5>
									    <p>{l s='Quick and simple single opt-in contact list uploads.' mod='getresponse'}</p>
									    <div class="btn-wrap">
									        <a href="http://app.getresponse.com/track_customer.html?tid=42501" class="bt" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=41801"> {l s='Learn more' mod='getresponse'}</a>
									    </div>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="reson r5 flex-col">
									<h3><i>5</i><span>{l s='One-Click Inbox Preview' mod='getresponse'}</span></h3>
									<div class="img flex-grow"></div>
									<div class="bxHover">
									    <h5>{l s='One-Click Inbox Preview' mod='getresponse'}</h5>
									    <p>{l s='Simple rendering test: one click - many previews, includes mobile devices.' mod='getresponse'}</p>
									    <div class="btn-wrap">
									        <a href="http://app.getresponse.com/track_customer.html?tid=42501" class="bt" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=41901"> {l s='Learn more' mod='getresponse'}</a>
									    </div>
									</div>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="reson r6 flex-col">
									<h3><i>6</i><span>{l s='Marketing Automation' mod='getresponse'}</span></h3>
									<div class="img flex-grow"></div>
									<div class="bxHover">
									    <h5>{l s='Marketing Automation' mod='getresponse'}</h5>
									    <p>{l s='Time-based and action-based messages.' mod='getresponse'}</p>
									    <div class="btn-wrap">
									        <a href="http://app.getresponse.com/track_customer.html?tid=42501" class="bt" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=42001"> {l s='Learn more' mod='getresponse'}</a>
									    </div>
									</div>
								</div>
							</div>
						</div>
						<div class="row row-flex row-flex-wrap gutter-5">
							<div class="col-md-3">
								<div class="reson r7 flex-col">
									<h3><i>7</i><span>{l s='Mobile Apps' mod='getresponse'}</span></h3>
									<div class="img flex-grow"></div>
									<div class="bxHover">
									    <h5>{l s='Mobile Apps' mod='getresponse'}</h5>
									    <p>{l s='Email marketing on the go, for your iPhone and Android' mod='getresponse'}</p>
									    <div class="btn-wrap">
									        <a href="http://app.getresponse.com/track_customer.html?tid=42501" class="bt" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=42101"> {l s='Learn more' mod='getresponse'}</a>
									    </div>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="reson r8 flex-col">
									<h3><i>8</i><span>{l s='Verified Deliverability' mod='getresponse'}</span></h3>
									<div class="img flex-grow"></div>
									<div class="bxHover">
									    <h5>{l s='Verified Deliverability' mod='getresponse'}</h5>
									    <p>{l s='Up to 99.5%% deliverability with solutions to keep your emails out of the junk box.' mod='getresponse'}</p>
									    <div class="btn-wrap">
									        <a href="http://app.getresponse.com/track_customer.html?tid=42501" class="bt" title="Try It Free" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=42201"> {l s='Learn more' mod='getresponse'}</a>
									    </div>
									</div>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="reson r9 flex-col">
									<h3><i>9</i><span>{l s='Email Analytics' mod='getresponse'}</span></h3>
									<div class="img flex-grow"></div>
									<div class="bxHover">
									    <h5>{l s='Email Analytics' mod='getresponse'}</h5>
									    <p>{l s='Insightful, easy-to-read, all-inclusive graphs and charts.' mod='getresponse'}</p>
									    <div class="btn-wrap">
									        <a href="http://app.getresponse.com/track_customer.html?tid=42501" class="bt" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=42301"> {l s='Learn more' mod='getresponse'}</a>
									    </div>
									</div>
								</div>
							</div>
						</div>
						<div class="row row-flex row-flex-wrap gutter-5">
							<div class="col-lg-12">
								<div class="reson r10 flex-col">
									<h3><i>10</i><span>{l s='1000+ Free iStockphoto images' mod='getresponse'}</span></h3>
									<div class="img flex-grow"></div>
									<div class="bxHover">
									    <h5>{l s='1000+ Free iStockphoto images' mod='getresponse'}</h5>
									    <p>{l s='Multiple themes, simple drag\'n\'drop edition inside newsletter.' mod='getresponse'}</p>
									    <div class="btn-wrap">
									        <a href="http://app.getresponse.com/track_customer.html?tid=42501" class="bt" title="{l s='Try It Free' mod='getresponse'}" target="_blank">{l s='Try It Free' mod='getresponse'}</a>{l s='or' mod='getresponse'}<a target="_blank" href="http://app.getresponse.com/track_customer.html?tid=42401"> {l s='Learn more' mod='getresponse'}</a>
									    </div>
									</div>
								</div>
							</div>
						</div>
				</div>
				<div class="join-us">
					<h2>{l s='World\'s Easiest Email Marketing.' mod='getresponse'}</h2>
					<div class="btns">
						<a href="http://app.getresponse.com/track_customer.html?tid=42501" class="btn" title="{l s='Start Free Trial' mod='getresponse'}" target="_blank">{l s='Start Free Trial' mod='getresponse'}</a>
						<small>{l s='30-day free trial. No credit card required.' mod='getresponse'}</small>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>
