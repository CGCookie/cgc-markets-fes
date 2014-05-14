CGC Markets FES
===============

This plugin contains all EDD Frontend Submissions tweaks for CGC Markets.

Each "feature set" is contained within it's own class, and each is documented below.

## Development Fund - includes/class-dev-fund.php ##

This class handles all modifications necessary to provide vendors the option of opting into the Blender development fund. Proceeds from sales of vendors that have opted in get tracked to a "dev fund" user.

To setup dev fund tracking, do the following:

1. Specify the ID of the dev fund user account. This user ID is set in Downloads > Settings > Misc.

2. Create a field in the Submission form with the following options:
- Required: Yes
- Field Label: (anything)
- Meta Key: dev_fund
- Help Text: (anything)
- Options: No, Yes 

3. When a vendor opts into the dev fund, his/her product will be tagged with a tag of "dev-fund". This tag can be used to query all products that are contributing to the dev fund.