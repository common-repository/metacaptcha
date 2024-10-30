=== MetaCAPTCHA ===
Contributors: lenhattien
Donate link: metacaptcha.com
Tags: Proof of Work, anti-spam, MetaCAPTCHA
Requires at least: 3.0
Tested up to: 3.5.2
Stable tag: 2.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

MetaCaptcha uses the proof-of-work (or “client puzzle”) approach for combating spam. Proof-of-work approaches slow down spammers by requiring them to solve computational puzzles before accessing a desired service. The idea is to impose a per-access cost in hopes of reducing abuse. More specifically, the cost to spammers will be computational resources that they devote to solving a puzzle before sending each message. The approach was first outlined by Dwork and Naor. However, Laurie and Clayton demonstrated that a small constant cost would fail to significantly reduce spam and a large one would burden legitimate users. Later, Liu and Camp demonstrated that if puzzle difficulties were based on user reputations then a proof-of-work approach would indeed be successful. Thus, malicious clients (spammers) would need to be issued “harder” (longer to solve) puzzles than non- malicious clients.
The new comments on your blog will be sent to our MetaCAPTCHA server for spam classification.

== Installation ==

1. Upload folder `metacaptcha` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Register an account at http://www.metacaptcha.com/metacaptcha/documentation/registration/ and get the PRIVATE_KEY and PUBLIC_ID.
Replace your PRIVATE_KEY and PUBLIC_ID at line 3 and 4 of metacaptcha_lib.php
4. Done

== Upgrade Notice ==
= 2.7 =
Migrate metacaptcha services to the new server
= 2.6 =
Remove config page for manually input PRIVATE_KEY and PUBLIC_ID in metacaptcha_lib.php
Fix readme.txt
= 2.5 =
fix typo
= 2.4 =
MetaCAPTCHA plugin has a new name
= 2.3 =
* New version is out!
= 2.2 =
* Please upgrade to the new version
= 2.1 =
* The old plugin may not work with the new model

== Changelog ==
= 2.3 =
* Add useful computation fishy
* Add exchange shared secret key

= 2.2 =
* Fix readme.txt

= 2.1 =
* Fix readme.txt
* Simplify the cookies model
* Storing local score and timestamp

= 2.0 =
* Update code so it can work with MetaCAPTCHA protocol located on rabbit.cs.pdx.edu
* Reload page after verifying cookies complete
