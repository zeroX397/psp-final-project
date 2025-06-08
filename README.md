# Peaceful World - Web-based Management Application

This is repository for final project of Programming for Security Professional.

This repo contains a simple application about sales management system based on PHP and MySQL. The web-app implement security to prevent the OWASP Top 10 Vulnerabilities 2021 (as we created this repo) such as:

1. Broken Access Control 
   - Prevention : applying role-checking in the application. Users' also have their role saved in the database.

2. Cryptographic Failure
   - Prevention : applying strong encryption algorithm for password in database. Also do not forget to install proper SSL Certificate to secure data transmission.
  
3. Injection
   - Prevention : applying sanitation and escape character for special characters that potentially being used to inject malicious scripts.

4. Insecure Design
   - Prevention : properly validating users' input.

5. Security Misconfiguration
   - Prevention : properly setting up the configuration, regularly update the dependencies

6. Vulnerable and Outdated Components
   - 

7. Identification and Authentication Failures
   - 

8. Software and Data Integrity Failures
   - 

9. Security Logging and Monitoring Failures
   - 

10. Server-Side Request Forgery
    - 


We use PHP 8.4 and MySQL 9 when developing this application. If you want to use this repo as demo, please make sure your PHP is using the same version or higher.