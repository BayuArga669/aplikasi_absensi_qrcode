This document outlines the functional specifications for a digital attendance system that utilizes QR code scanning and geographic location (geolocation) validation to record employee attendance.

🚀 Key Features by User Role
The system has three primary user roles with distinct access rights and features:

1. Admin
The Admin has full control over the system and is responsible for managing master data and overall operations.

Main Dashboard: Displays a real-time summary of attendance statistics (number of employees present, late, on leave, or sick).

Employee Management: The ability to add, edit, and deactivate employee data along with their details (name, position, superior, etc.).

Dynamic QR Code Generator:

Generates a unique QR code to be displayed on a screen at the office.

This QR code will automatically refresh at set intervals (e.g., every 30 seconds) to prevent misuse.

Comprehensive Report Management:

View daily, weekly, and monthly attendance recaps for all employees.

Search and filter attendance data by name, date, or department.

Export reports to Excel or PDF format.

Leave/Sick Request Approval: Manage and approve or reject leave/sick requests from employees.

2. Superior
The Superior has limited access to monitor the team under their supervision.

Team Dashboard: View an attendance summary of their team members.

Late Arrival Notifications: Receive real-time push notifications when a team member clocks in past the designated time.

Team Attendance Monitoring: View a list of team members who have checked in for the day.

Late Arrival Reports: Access a specific report that only displays the tardiness data of their team members.

3. Employee (User)
Employees use the mobile application to perform attendance activities and manage their personal data.

Attendance via QR Code Scan:

The primary feature for scanning the QR code at the office.

The system will automatically capture the user's GPS location during the scan.

Geolocation Validation: The system will validate whether the user's GPS location is within a predetermined radius of the office location. Attendance will only be successful if the location is valid.

Submitting Leave or Sick Requests:

Request time off (leave or sick) through a form in the application.

Ability to attach supporting files (e.g., a doctor's note).

Attendance History: View their personal attendance records (check-in times, check-out times, and attendance status).

⚙️ User Flow
Here are the main workflows for using the application:

1. Check-in Flow
The Employee arrives at the office area and opens the application on their phone.

The employee presses the "Scan for Check-in" button.

The application will activate the camera and request location access (GPS).

The employee points the camera at the QR code displayed on the screen at the office.

The application simultaneously captures the QR code data and the employee's GPS coordinates.

The data is sent to the server for validation:

QR Code Validation: The server checks if the code is valid and matches the one currently generated.

Location Validation: The server checks if the employee's GPS coordinates are within the allowed radius of the office (e.g., 50 meters).

If successful: The system records the attendance time, and the employee receives a "Check-in Successful" notification.

If it fails: The employee receives an error message (e.g., "You are outside the office area" or "Invalid QR Code").

If late: The system still records the attendance and automatically sends a notification to the respective Superior.

2. Leave/Sick Request Flow
The Employee opens the application and navigates to the "Request Leave/Sick" menu.

The employee fills out a form containing the type of request (Leave/Sick), dates, and a description.

The employee can upload a supporting document if required.

Once submitted, the request is logged in the system, and the Admin will receive a notification to review the submission.

3. Superior's Monitoring Flow
The Superior receives a notification when a team member is late.

The Superior can open the application or web dashboard to see the list of team members who are present for the day.

The Superior can navigate to the "Late Arrivals Report" menu to see details of who was late and at what time.