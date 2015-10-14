<div class="field">
	<label>About Me</label>
	<div id="editProfileContainer" >
		<div id="charCount" class="ui bottom mini left attached label">400 characters left</div>
		<textarea id="profileDescription" class="editor" placeholder=""></textarea>
	</div>
</div>
<div class="two fields">
	<div class="field">
		<label>Zip Code</label>
		<input type="text" placeholder="Your Zip Code" id="zipcode">
	</div>
	<div class="field">
		<label>First Name</label>
		<input type="text" placeholder="First Name" id="firstName">
	</div>
</div>
<div class="ui simple divider"></div>
<div class="field">
	<label>Birthday</label>
	<div class="three fields">
		<div class="field">
			<div id="_birthMonth" class="ui selection dropdown">
				<input id="birthMonth" name="birthMonth" type="hidden">
				<div class="default text">Month</div>
				<i class="dropdown icon"></i>
				<div class="menu">
					<div class="item" data-value="1">January</div>
					<div class="item" data-value="2">February</div>
					<div class="item" data-value="3">March</div>
					<div class="item" data-value="4">April</div>
					<div class="item" data-value="5">May</div>
					<div class="item" data-value="6">June</div>
					<div class="item" data-value="7">July</div>
					<div class="item" data-value="8">August</div>
					<div class="item" data-value="9">September</div>
					<div class="item" data-value="10">October</div>
					<div class="item" data-value="11">November</div>
					<div class="item" data-value="12">December</div>
				</div>
			</div>
		</div>
		<div class="field">
			<div id="_birthDay" class="ui selection dropdown">
				<input id="birthDay" name="birthDay" type="hidden">
				<div class="default text">Day</div>
				<i class="dropdown icon"></i>
				<div class="menu">
					<?php  

						for ($i=1; $i < 32 ; $i++) { 
							
							echo '<div class="item" data-value="' . $i . '">' . $i . '</div>';

						}
					?>
				</div>
			</div>
		</div>
		<div class="field">
			<div id="_birthYear" class="ui selection dropdown">
				<input id="birthYear" name="birthYear" type="hidden">
				<div class="default text">Year</div>
				<i class="dropdown icon"></i>
				<div class="menu">
					<?php  

						$y = date("Y");
						$y = intval($y);
						$max_bday = $y - 17;
						$min_bday = $y - 90;

						for ($i=$min_bday; $i < $max_bday ; $i++) { 
							
							echo '<div class="item" data-value="' . $i . '">' . $i . '</div>';

						}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="ui simple divider"></div>
<div class="three fields">
	<div class="field">
		<label>Relationship Status</label>
		<div id="_relationshipStatus" class="ui selection dropdown">
			<input id="relationshipStatus" name="relationshipStatus" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="1">Single</div>
				<div class="item" data-value="2">Single and taking a break from dating</div>
				<div class="item" data-value="3">In a relationship</div>
				<div class="item" data-value="4">It's complicated</div>
				<div class="item" data-value="5">Here for friends only</div>
				<div class="item" data-value="6">I'm in love</div>
				<div class="item" data-value="7">No longer available</div>
				<div class="item" data-value="8">Married</div>
				<div class="item" data-value="9">Separated</div>
				<div class="item" data-value="10">In an open relationship</div>
			</div>
		</div>
	</div>
	<div class="field">
		<label>My Gender</label>
		<div id="_gender" class="ui selection dropdown">
			<input id="gender" name="gender" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="guy">Guy</div>
				<div class="item" data-value="gal">Gal</div>
			</div>
		</div>
	</div>
	<div class="field">
		<label>I'm Looking For</label>
		<div id="_seekingGender" class="ui selection dropdown">
			<input id="seekingGender" name="seekingGender" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="guy">Guy</div>
				<div class="item" data-value="gal">Gal</div>
				<div class="item" data-value="guyGal">Guys and Gals</div>
			</div>
		</div>
	</div>
</div>
<div class="ui simple divider"></div>
<div class="three fields">
	<div class="field">
		<label>Height</label>
		<div id="_height" class="ui selection dropdown">
			<input id="height" name="height" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="1">Less than 5'</div>
				<div class="item" data-value="2">5'0"</div>
				<div class="item" data-value="3">5'1"</div>
				<div class="item" data-value="4">5'2"</div>
				<div class="item" data-value="5">5'3"</div>
				<div class="item" data-value="6">5'4"</div>
				<div class="item" data-value="7">5'5"</div>
				<div class="item" data-value="8">5'6"</div>
				<div class="item" data-value="9">5'7"</div>
				<div class="item" data-value="10">5'8"</div>
				<div class="item" data-value="11">5'9"</div>
				<div class="item" data-value="12">5'10"</div>
				<div class="item" data-value="13">5'11"</div>
				<div class="item" data-value="14">6'0"</div>
				<div class="item" data-value="15">6'1"</div>
				<div class="item" data-value="16">6'2"</div>
				<div class="item" data-value="17">6'3"</div>
				<div class="item" data-value="18">6'4"</div>
				<div class="item" data-value="19">6'5"</div>
				<div class="item" data-value="20">6'6"</div>
				<div class="item" data-value="21">6'7"</div>
				<div class="item" data-value="22">6'8"</div>
				<div class="item" data-value="23">6'9"</div>
				<div class="item" data-value="24">6'10"</div>
				<div class="item" data-value="25">6'11"</div>
				<div class="item" data-value="26">7'0"</div>
				<div class="item" data-value="27">7'1"</div>
				<div class="item" data-value="28">7'2"</div>
				<div class="item" data-value="29">7'3"</div>
				<div class="item" data-value="30">7'4"</div>
				<div class="item" data-value="31">7'5"</div>
				<div class="item" data-value="32">7'6"</div>
				<div class="item" data-value="33">7'7"</div>
				<div class="item" data-value="34">7'8"</div>
				<div class="item" data-value="35">Really tall - more than 7'8"</div>

			</div>
		</div>
	</div>
	<div class="field">
		<label>Eye Color</label>
		<div id="_eyeDesc" class="ui selection dropdown">
			<input id="eyeDesc" name="eyeDesc" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="blue">Blue</div>
				<div class="item" data-value="brown">Brown</div>
				<div class="item" data-value="gray">Gray</div>
				<div class="item" data-value="green">Green</div>
				<div class="item" data-value="hazel">Hazel</div>
				<div class="item" data-value="nothing">Prefer not to say</div>
			</div>
		</div>
	</div>
	<div class="field">
		<label>Body Type</label>
		<div id="_bodyType" class="ui selection dropdown">
			<input id="bodyType" name="bodyType" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="athletic">Athletic</div>
				<div class="item" data-value="average">Average</div>
				<div class="item" data-value="beerGut">Beer gut</div>
				<div class="item" data-value="bigStrong">Big but really strong</div>
				<div class="item" data-value="curvy">Curvy in all the right places</div>
				<div class="item" data-value="fatHappy">Fat and happy</div>
				<div class="item" data-value="funSize">Fun size</div>
				<div class="item" data-value="healthyFit">Healthy and fit</div>
				<div class="item" data-value="someAbs">I can see some of my abs</div>
				<div class="item" data-value="jacked">Jacked</div>
				<div class="item" data-value="longLean">Long and lean</div>
				<div class="item" data-value="overweightWorking">Overweight but I'm working on it</div>
				<div class="item" data-value="sixPack">Six pack abs</div>
				<div class="item" data-value="slightlyOverweight">Slightly overweight but that's ok</div>
				<div class="item" data-value="stocky">Stocky</div>
				<div class="item" data-value="thin">Thin</div>
				<div class="item" data-value="voluptuous">Voluptuous</div>
				<div class="item" data-value="nothing">Prefer not to say</div>
			</div>
		</div>
	</div>
</div>
<div class="two fields">
	<div class="field">
		<label>Hair</label>
		<div id="_hairDesc" class="ui selection dropdown">
			<input id="hairDesc" name="hairDesc" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="auburn">Auburn</div>
				<div class="item" data-value="balding">Balding</div>
				<div class="item" data-value="black">Black</div>
				<div class="item" data-value="blond">Blond</div>
				<div class="item" data-value="brown">Brown</div>
				<div class="item" data-value="brunette">Brunette</div>
				<div class="item" data-value="ginger">Ginger</div>
				<div class="item" data-value="fireyRed">Firey Red</div>
				<div class="item" data-value="full">Full and Lush</div>
				<div class="item" data-value="mohawk">Mohawk</div>
				<div class="item" data-value="multi">Multi-color</div>
				<div class="item" data-value="saltPepper">Salt and Pepper</div>
				<div class="item" data-value="sandy">Sandy</div>
				<div class="item" data-value="shaved">Shaved</div>
				<div class="item" data-value="silver">Silver Fox</div>
				<div class="item" data-value="nothing">Prefer not to say</div>
			</div>
		</div>
	</div>
	<div class="field">
		<label>Ethnicity</label>
		<div id="_ethnicity" class="ui selection dropdown">
			<input id="ethnicity" name="ethnicity" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="asian">Asian</div>
				<div class="item" data-value="black">Black</div>
				<div class="item" data-value="indian">Indian</div>
				<div class="item" data-value="latino">Latino/Hispanic</div>
				<div class="item" data-value="middleEast">Middle Eastern</div>
				<div class="item" data-value="mixed">Mixed Race</div>
				<div class="item" data-value="native">Native American</div>
				<div class="item" data-value="other">Other</div>
				<div class="item" data-value="pacificIslander">Pacific Islander</div>
				<div class="item" data-value="white">White</div>
				<div class="item" data-value="nothing">Prefer not to say</div>
			</div>
		</div>
	</div>
</div>
<div class="ui simple divider"></div>
<div class="three fields">
	<div class="field">
		<label>Faith</label>
		<div id="_religious" class="ui selection dropdown">
			<input id="religious" name="religious" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="agnostic">Agnostic</div>
				<div class="item" data-value="atheist">Atheist</div>
				<div class="item" data-value="buddhist">Buddhist</div>
				<div class="item" data-value="catholic">Catholic</div>
				<div class="item" data-value="christian">Christian</div>
				<div class="item" data-value="hindu">Hindu</div>
				<div class="item" data-value="jewish">Jewish</div>
				<div class="item" data-value="lds">LDS</div>
				<div class="item" data-value="muslim">Muslim</div>
				<div class="item" data-value="notReligious">Not religious</div>
				<div class="item" data-value="other">Other</div>
				<div class="item" data-value="spiritual">Spiritual but not religious</div>
				<div class="item" data-value="nothing">Prefer not to say</div>
			</div>
		</div>
	</div>
	<div class="field">
		<label>Children</label>
		<div id="_children" class="ui selection dropdown">
			<input id="children" name="children" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				
				<div class="item" data-value="nope">No, I don't have kids</div>
				<div class="item" data-value="noNo">No and I don't want any</div>
				<div class="item" data-value="NoYes">No and I want some</div>
				<div class="item" data-value="NoOk">No and it's ok if you have kids</div>
				<div class="item" data-value="yes">Yes, I have kids</div>
				<div class="item" data-value="yesNo">Yes and I don't want more</div>
				<div class="item" data-value="yesMore">Yes and I want more</div>
				<div class="item" data-value="yesOk">Yes and it's ok if you have kids</div>
				<div class="item" data-value="nothing">Prefer not to say</div>
			</div>
		</div>
	</div>
	<div class="field">
		<label>Income</label>
		<div id="_income" class="ui selection dropdown">
			<input id="income" name="income" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="25">Less than $25,000</div>
				<div class="item" data-value="2540">$25,000 to $40,000</div>
				<div class="item" data-value="4060">$40,000 to $60,000</div>
				<div class="item" data-value="6080">$60,000 to $80,000</div>
				<div class="item" data-value="80100">$80,000 to $100,000</div>
				<div class="item" data-value="100more">More than $100,000</div>
				<div class="item" data-value="nothing">Prefer not to say</div>
				
			</div>
		</div>
	</div>
</div>
<div class="ui simple divider"></div>
<div class="three fields">
	<div class="field">
		<label>Smoking</label>
		<div id="_smokerPref" class="ui selection dropdown">
			<input id="smokerPref" name="smokerPref" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="cigars">Cigars are cool</div>
				<div class="item" data-value="420">420 occasionally</div>
				<div class="item" data-value="420Nothing">420 friendly but nothing else</div>
				<div class="item" data-value="noWay">No Way!</div>
				<div class="item" data-value="noNo">No and I prefer if you didn't</div>
				<div class="item" data-value="noYes">No but you can</div>
				<div class="item" data-value="yesAllTime">Yes! All the time</div>
				<div class="item" data-value="yesQuitting">Yes but I'm trying to quit</div>
				<div class="item" data-value="yesDiscreetly">Yes, discreetly</div>
				<div class="item" data-value="yesWhile">Yes, once in a while</div>
				<div class="item" data-value="yesDrink">Yes, only when I drink</div>
				<div class="item" data-value="nothing">Prefer not to say</div>
			</div>
		</div>
	</div> 
	<div class="field">
		<label>Drinking</label>
		<div id="_drinkingPref" class="ui selection dropdown">
			<input id="drinkingPref" name="drinkingPref" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				
				<div class="item" data-value="no">No, I don't drink.</div>
				<div class="item" data-value="noOk">No, but it's ok if you do.</div>
				<div class="item" data-value="noNo">No, and I'd rather not be around it.</div>
				<div class="item" data-value="yesPlease">Yes please!</div>
				<div class="item" data-value="yesSocially">Yes, socially</div>
				<div class="item" data-value="yesWeekend">Yes, just on the weekends</div>
				<div class="item" data-value="yesEveryday">Yes, everyday</div>
				<div class="item" data-value="yesBeerPong">Yes! Beer pong anybody?</div>
				<div class="item" data-value="nothing">Prefer not to say</div>
			</div>
		</div>
	</div>
	<div class="field">
		<label>Astrological Sign</label>
		<div id="_zodiacPref" class="ui selection dropdown">
			<input id="zodiacPref" name="zodiacPref" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="YES">Show my sign</div>
				<div class="item" data-value="NO">Do not show my sign</div>
			</div>
		</div>
	</div> 
</div>
<div class="ui simple divider"></div>
<div class="two fields">
	<div class="field">
		<label>Profile Rating</label>
		<div id="_profileRating" class="ui selection dropdown">
			<input id="profileRating" name="profileRating" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="noNudity">There is NO NUDITY on my profile</div>
				<div class="item" data-value="yesNudity">ADULT - I will feature nudity</div>
			</div>
		</div>
	</div> 
	<div class="field">
		<label>Adult Preference</label>
		<div id="_adultPreference" class="ui selection dropdown">
			<input id="adultPreference" name="adultPreference" type="hidden">
			<div class="default text">Please Choose</div>
			<i class="dropdown icon"></i>
			<div class="menu">
				<div class="item" data-value="noNudity">I DO NOT want to see nudity.</div>
				<div class="item" data-value="yesNudity">Profiles with nudity is OK.</div>

			</div>
		</div>
	</div>
</div>
<div style="height:20px;"></div>
<div class="profileEditFooter">
	<div class="profileEditFooterInner">
		<p><strong>Adult Profiles</strong> - If you choose to feature nudity or adult content on your profile, please be sure to identify it as <strong>Adult</strong>. This gives other users the ability to choose whether this is content they wish to view.</p>
	</div>
</div>







