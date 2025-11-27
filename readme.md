# 🚀 PHP MASS MAIL BLASTER

**PHP MASS MAIL BLASTER** is a lightweight, secure, and modern PHP application for sending mass emails using SMTP. It allows you to upload a CSV list of recipients and send personalized HTML emails using dynamic placeholders (e.g., `{{name}}`, `{{code}}`).



## ✨ Features

* **CSV Driven:** Upload a CSV file to automatically populate your recipient list.
* **Dynamic Variables:** Personalize emails by mapping CSV headers to HTML placeholders (e.g., converts `{{name}}` to "John Doe").
* **Secure SMTP:** Uses PHPMailer for reliable delivery via Gmail, Outlook, or custom SMTP servers.
* **Advanced Routing:** Support for CC, BCC, and Custom Reply-To addresses.
* **Modern UI:** A clean, responsive "Glassmorphism" interface.
* **No Database Required:** Runs entirely on PHP and local files.

## 📂 Directory Structure

Ensure your project folder looks like this:

```text
/php-dynamic-mass-mailer
├── index.php            # The main application file
├── README.md            # Documentation
├── PHPMailer/           # The PHPMailer library folder
└── index.php           # Entry Point
```
Installation
Clone the Repo:

Bash

git clone [https://github.com/Abhisek-Chowdhury-19/php-csv-mass-mailer](https://github.com/Abhisek-Chowdhury-19/php-csv-mass-mailer)
Install PHPMailer:


Run the Server: Place the folder in your htdocs (XAMPP/MAMP) or run via terminal:

Bash
```
php -S localhost:8000
```
📖 Usage Guide
1. The CSV Format
Your CSV file must have headers in the first row. The script uses these headers to find data.

Example list.csv:

Code snippet

email,name,discount_code,city
john@example.com,John Doe,SAVE20,New York
jane@test.com,Jane Smith,WELCOME50,London
2. Writing the Email
In the "HTML Message Body" section of the app, use double curly braces (e.g., `{{name}}`) to insert data from your CSV.

Example Input:

HTML

<p>Hello <strong>{{name}}</strong>,</p>
<p>We noticed you are visiting {{city}}. Here is a specialized code for you: <b>{{discount_code}}</b>.</p>
Result Sent to John:

Hello John Doe, We noticed you are visiting New York. Here is a specialized code for you: SAVE20.

3. SMTP Configuration (Gmail Example)
To use Gmail, you cannot use your login password. You must generate an App Password:

Go to your Google Account > Security.

Enable "2-Step Verification".

Search for "App Passwords".

Generate a new password and paste it into the App Password field in SwiftBlast.

⚠️ Limitations & Privacy
Sending Limits: Gmail and standard SMTP servers have daily sending limits (usually 500 emails/day for free Gmail).

Timeout: If sending to thousands of users, you may need to increase your PHP max_execution_time.

🤝 Contributing
Feel free to submit issues and enhancement requests.

Fork the repository

Create your Feature Branch (git checkout -b feature/AmazingFeature)

Commit your changes (git commit -m 'Add some AmazingFeature')

Push to the branch (git push origin feature/AmazingFeature)

Open a Pull Request

📄 License
Distributed under the MIT License.


### 3. Next Step (Interactive)
Would you like me to generate a **sample.csv** file content that you can upload to the repository as well, so users have a template to test with immediately?
