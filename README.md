# admin.groenproductions
Sites Management Tool for Groen Productions
Note: This tool is designed before any potential website for Groen Productions
Purpose: to allow clients to maintain the dynamic content of their websites.

Design: 
- a product-independent cms
- use wp only for user mgmt and security
- minimal number of databases
- SMT4GP uses 1 database for both test and production: no continuous access is required
- client login will determine if it is their test account or their live account
- only functionality for client's website loads
- admin can see all
- multi-lingual: en, nl, es

Stages:

*I am here*

1) migrate CuscoNow Subscriber functionality, strip off additional (subscription) functionality
2) create additional Domains sections
3) test the coupling/decoupling of subscribers with wp_users
4) create framework where users only can get access to their own product and no other logic loads.
5) create fake third project (with 2 databases) to see if this works
