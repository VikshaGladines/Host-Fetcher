# Host Fetcher
***

Host Fetcher is an app use internally by HostFamillyStay to help them to find the nearest family hosts from a given university
This app has two parts, one fetch the top 10 nearest hosts from a given univeristy, the other is an admin part to update / add / delete hosts and universities travels in a saved database

***

This app have a MySql database with two tables, one with all london universities informations and the other one with all family hosts informations and their postcode.
It use the TFL API. TFL is the company which manages the public transports in London. 
Their API is used to know the commute time between two places.

***

You can write any university information (postcode, address or name) in the search bar, 
the app then show the 10 closest family hosts with their informations (postcode, number of beds, meal plans, beds number and the commute time (in minutes)) sorted from nearest to farthest.

You can also manage the database using the update page.
You can choose to update or delete a given travel information in the database or update all of it.

***

Technologies used : PHP, JavaScript, HTML, CSS, MySql
