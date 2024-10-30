=== Invoicing for economic ===
Contributors: postechdk
Tags: Woocommerce, e-conomic, invoice, accounting, sync
Requires at least: 5.3
Tested up to: 5.8.3
Stable tag: 1.0.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send orders from your Woocommerce based webshop to your e-conomic accounting system as invoice drafts

== Description ==
Save time by sending orders from Woocommerce to your e-conomic by the click of a button.
Orders can be send in bulks via the Woocommerce orders screen.
They are send to e-conomic as invoice drafts, allowing you to check them before booking them.
This invoice draft contains 1 invoiceline, and the price is the order total.
Neither products nor customers are synced between Woocommerce and e-conomic.
This plugin uses the REST API that is available with an active subscription to e-conomic accounting system .
For more information, see following links:
e-conomic : https://e-conomic.com
Terms of use : https://www.e-conomic.dk/abonnementsvilkaar-e-conomic
Privacy policy : https://www.e-conomic.dk/sikkerhed/privacy
REST API endpoint : https://restapi.e-conomic.com/
REST API doccumentation : https://www.e-conomic.com/developer/documentation


== Requirements ==
* A working Woocommerce based webshop
* An active subscription to e-conomic accounting system.

== Screenshots ==

1. To be able to use this plugin, you need to allow it to access your e-conomic.
For that you need to create an e-conomic developer account, which will give you the 2 required tokens.
Go to https://e-conomic.com/developer and sign up. Dont worry, its free.
You will get a confirmation email from e-conomic containing your login information.
Once logged in, proceed to next step.
2. Click "Create a new app".
3. Enter a short name, it could be the name of your webshop.
Tick the "Sales" box. No ticks in "Required modules".
Then click "Create new app".
4. Now you have the first required token, copy the secret token and save it in a secure place.
You will need it later.
Click "Continue to tokens"
5. Copy the installation URL, but dont paste it in a browser yet.
Click "Save and close".
6. Confirm that the created app shows up with the name you gave it.
7. Now log out of the developer account, and log into your ordinary e-conomic account.
This is important.
Paste the installation URL in the address line in a browser, and hit enter.
Click "Add app".
If nothing happens, you are most likely still logged in with your developer account, if so click "change user".
8. Now you have the second required token. The Agreement Grant Token.
Copy it and save it.
Click "Go to e-conomic"
9. Go to Settings / Extensions / Apps.
Confirm that the app shows up in the list.
Congratulations, you can now proceed to setup the plugin.
10. Go to Woocommerce Settings / Invoicing / Tokens.
Enter the 2 tokens and click "Save changes".
If the tokens are valid, they will turn green... if not they will be red.
11. Click Setup.
The plugin needs to know something about your e-conomic before it can send orders.
First choose which e-conomic customer to use for the invoice draft.
You probably want to create a dedicated customer and call it "Webshop" or "Webshop customer" or something else.
After creating the customer hit the "F5" button to have it appear in the drop-down list.
Click "Save changes" and proceed.
12. Here you will need to choose which e-conomic product to use.
This will be the product appearing on the invoice draft, and the price will be the order total.
You will probably want to create a dedicated product in your e-conomic for this purpose.
When done, click "Save changes" and proceed.
13. Now choose an invoice layout from your e-conomic layouts.
It is not important which one, but you need to choose something.
Click "Save changes" and proceed.
14. At last, for each of the payment methods in your Woocommerce, choose a matching payment method in e-conomic.
Again, you probably want to create new payment methods in your e-conomic for this.
Click "Save changes" and proceed.
The plugin is mow fully setup.
15. To send orders, go to woocommerce / orders.
Select the orders you want to send, select "Send to e-conomic" in bulk actions, and click "Apply".
The selected orders should now appear in your e-conomic under "Invoices" ( But not yet booked ).
Confirm that the figures are correct before booking them.
16. This is how the invoice draft shows up in your e-conomic invoice overview.
17. This is the invoice draft.
As shown, it contains only 1 line ( even if the woocommerce order contains multiple products ).
The product price is the same as the order total excl. VAT.

== Changelog ==

= 1.0.1 =
* Bugfix

= 1.0.0 =
* Initial version