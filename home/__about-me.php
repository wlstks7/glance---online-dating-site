<div class="contentContainer contentContainerWithHeader">
	<div class="contentInner">
		<div class="contentHeader">
			<h3>Your Details</h3>
			<span>·</span>
			<a id="edit_profile_link" href="#">Edit Profile</a>
		</div>

		<!-- age and zodiac -->
		<div class="ui simple divider"></div>
		<div class="ui form contentAboutMe">
			<div class="two fields">
				<div class="field">
					<table>
						<tr>
							<td class="td_about about_strongText">Age: </td>
							<td class="td_about about_regularText">
								<?php echo $loggedUser["age"]; ?>
							</td>
						</tr>
					</table>
				</div>
				<div class="field">
					<table>
						<tr>
							<td class="td_about about_strongText"><?php echo $zodiacLabel; ?> </td>
							<td class="td_about about_regularText">
								<?php echo $zodiac; ?>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<!-- gender -->
			<div class="ui simple divider lighter"></div>
			<div class="field">
				<table>
					<tr>
						<td class="td_about about_strongText">Gender: </td>
						<td class="td_about about_regularText">
							<?php echo $gender . " interested in " . $seekingGender; ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- height and eyes -->
			<div class="ui simple divider lighter"></div>
			<div class="two fields">
				<div class="field">
					<table>
						<tr>
							<td class="td_about about_strongText">Height: </td>
							<td class="td_about about_regularText">
								<?php echo $height; ?>
							</td>
						</tr>
					</table>
				</div>
				<div class="field">
					<table>
						<tr>
							<td class="td_about about_strongText">Eyes: </td>
							<td class="td_about about_regularText">
								<?php echo $eyes; ?>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<!-- body type -->
			<div class="ui simple divider lighter"></div>
			<div class="field">
				<table>
					<tr>
						<td class="td_about about_strongText">Body Type: </td>
						<td class="td_about about_regularText">
							<?php echo $bodyType; ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- ethnicity and hair -->
			<div class="ui simple divider lighter"></div>
			<div class="two fields">
				<div class="field">
					<table>
						<tr>
							<td class="td_about about_strongText">Ethnicity: </td>
							<td class="td_about about_regularText">
								<?php echo $ethnicity; ?>
							</td>
						</tr>
					</table>
				</div>
				<div class="field">
					<table>
						<tr>
							<td class="td_about about_strongText">Hair: </td>
							<td class="td_about about_regularText">
								<?php echo $hair; ?>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<!-- income -->
			<div class="ui simple divider lighter"></div>
			<div class="field">
				<table>
					<tr>
						<td class="td_about about_strongText">Income: </td>
						<td class="td_about about_regularText">
							<?php echo $income; ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- faith -->
			<div class="ui simple divider lighter"></div>
			<div class="field">
				<table>
					<tr>
						<td class="td_about about_strongText">Faith: </td>
						<td class="td_about about_regularText">
							<?php echo $faith; ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- kids -->
			<div class="ui simple divider lighter"></div>
			<div class="field">
				<table>
					<tr>
						<td class="td_about about_strongText">Children: </td>
						<td class="td_about about_regularText">
							<?php echo $children; ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- smoking preference -->
			<div class="ui simple divider lighter"></div>
			<div class="field">
				<table>
					<tr>
						<td class="td_about about_strongText">Smoking: </td>
						<td class="td_about about_regularText">
							<?php echo $smokerPref; ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- drinking preference -->
			<div class="ui simple divider lighter"></div>
			<div class="field">
				<table>
					<tr>
						<td class="td_about about_strongText">Drinking: </td>
						<td class="td_about about_regularText">
							<?php echo $drinkingPref; ?>
						</td>
					</tr>
				</table>
			</div>
		</div>									
	</div>
</div>