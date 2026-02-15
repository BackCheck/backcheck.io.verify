# User Guide - BackCheck.io Verify

## Table of Contents
- [Getting Started](#getting-started)
- [User Roles and Permissions](#user-roles-and-permissions)
- [Common Workflows](#common-workflows)
- [Features by Role](#features-by-role)
- [Frequently Asked Questions](#frequently-asked-questions)

## Getting Started

### Logging In

1. Navigate to https://backcheck.io/verify
2. Enter your username and password
3. Click "Login"
4. You will be redirected to your role-specific dashboard

### Dashboard Overview

After logging in, you'll see your personalized dashboard with:
- **Quick Stats**: Summary of your active cases, pending tasks, and deadlines
- **Recent Activity**: Latest verification requests and updates
- **Action Items**: Tasks requiring your immediate attention
- **Notifications**: System alerts and messages

## User Roles and Permissions

The system supports 14 different user levels, each with specific permissions and access rights.

### 1. Super Admin (Level 1)

**Responsibilities**:
- Full system access and configuration
- User management (create, edit, delete users)
- System settings and configuration
- Database maintenance
- Integration management (Bitrix, Savvion, Google Sheets)

**Key Features**:
- Access to all modules and reports
- User role assignment
- System configuration
- API token management
- Audit log access

**Common Tasks**:
- Creating new user accounts
- Configuring system settings
- Managing integrations
- Generating system-wide reports
- Troubleshooting issues

---

### 2. Admin (Level 2)

**Responsibilities**:
- User and client management
- Company/client account setup
- Report generation and access
- System monitoring

**Key Features**:
- User management (limited to non-admin users)
- Client company management
- Access to all verification reports
- Dashboard analytics
- Export functionality

**Common Tasks**:
- Adding new client companies
- Creating client user accounts
- Generating monthly reports
- Monitoring verification progress
- Client communication

---

### 3. Team Lead (Level 3)

**Responsibilities**:
- Team management and supervision
- Work assignment and distribution
- Quality review and approval
- Performance monitoring

**Key Features**:
- Assign cases to analysts
- Review completed verifications
- Access team performance reports
- Approve/reject reports
- Re-assign cases

**Common Tasks**:
- Distributing new verification requests
- Reviewing analyst work
- Managing workload distribution
- Conducting quality checks
- Escalating complex cases

---

### 4. Senior Analyst (Level 4)

**Responsibilities**:
- Handle complex verification cases
- Mentor junior analysts
- Quality assurance
- Special investigations

**Key Features**:
- Access to all case types
- Priority case assignment
- Mentor/training mode
- Advanced search and filtering
- Report approval capability

**Common Tasks**:
- Processing complex verifications
- Assisting junior analysts
- Handling escalated cases
- Conducting detailed investigations
- Generating comprehensive reports

---

### 5. Analyst (Level 5)

**Responsibilities**:
- Process standard verification requests
- Document verification
- Data collection and validation
- Report preparation

**Key Features**:
- Case assignment view
- Document upload and management
- Status updates
- Basic report generation
- Communication tools

**Common Tasks**:
- Reviewing assigned verification requests
- Contacting employers/institutions
- Collecting supporting documents
- Updating case status
- Preparing verification reports

---

### 6. Quality Control (Level 6)

**Responsibilities**:
- Review and approve completed verifications
- Ensure quality standards
- Reject incomplete or inaccurate reports
- Provide feedback to analysts

**Key Features**:
- QC queue view
- Approve/reject interface
- Feedback mechanism
- Quality metrics dashboard
- Report revision requests

**Common Tasks**:
- Reviewing completed reports
- Checking data accuracy
- Approving finalized reports
- Requesting revisions
- Maintaining quality standards

---

### 7. Client Admin (Level 7)

**Responsibilities**:
- Manage client company portal
- Submit verification requests
- Monitor team's verification status
- Generate client reports

**Key Features**:
- Bulk upload capability
- Client dashboard
- User management (client users only)
- Report access
- Invoice/billing view

**Common Tasks**:
- Submitting new verification requests
- Bulk uploading applicant data
- Monitoring verification progress
- Downloading reports
- Managing client user accounts

---

### 8. Client User (Level 8)

**Responsibilities**:
- Submit individual verification requests
- Track verification status
- Download completed reports

**Key Features**:
- Submit verification form
- Status tracking
- Report download
- Document upload
- Limited dashboard view

**Common Tasks**:
- Creating new verification requests
- Uploading applicant documents
- Checking verification status
- Downloading completed reports
- Communicating with support

---

### 9. Finance (Level 9)

**Responsibilities**:
- Billing and invoicing
- Payment tracking
- Financial reporting
- Credit management

**Key Features**:
- Billing dashboard
- Invoice generation
- Payment status tracking
- Financial reports
- Credit/debit notes

**Common Tasks**:
- Generating monthly invoices
- Tracking payments
- Managing client credits
- Financial reconciliation
- Generating financial reports

---

### 10-14. Specialized Roles

Custom roles can be configured for specific organizational needs:
- Regional managers
- Compliance officers
- Training coordinators
- Support staff
- Custom workflows

## Common Workflows

### Workflow 1: Submitting a Verification Request (Client User)

1. **Login** to the client portal
2. Navigate to **"New Verification"** or **"Submit Check"**
3. Fill in the required information:
   - Client Reference Number
   - Applicant Name
   - Contact Information (Email, Phone)
   - Check Type (Employment, Education, etc.)
   - Additional Details
4. **Upload Documents** (if required):
   - Resume/CV
   - ID Card Copy
   - Supporting certificates
5. **Review** the information
6. Click **"Submit"**
7. **Receive Confirmation**: Note the verification ID for tracking

**Tips**:
- Keep your client reference numbers unique and consistent
- Upload clear, legible document scans
- Provide complete contact information for faster processing
- Use bulk upload for multiple verifications

---

### Workflow 2: Processing a Verification (Analyst)

1. **Login** to your analyst dashboard
2. View **"Assigned Cases"** or **"My Queue"**
3. **Select a Case** to work on
4. **Review** applicant information and requirements
5. **Collect Information**:
   - Contact employer/institution
   - Request verification documents
   - Verify provided information
6. **Update Status** as you progress:
   - Initial Investigation
   - Contact Attempted
   - Information Received
   - Verification in Progress
7. **Upload Evidence**:
   - Email correspondence
   - Verification letters
   - Supporting documents
8. **Prepare Report**:
   - Fill in verification form
   - Add findings and observations
   - Provide recommendation
9. **Submit for QC Review**
10. **Address QC Feedback** (if required)

**Tips**:
- Update case status regularly
- Document all communication attempts
- Upload all supporting evidence
- Be thorough and accurate in your reports
- Meet TAT (Turnaround Time) deadlines

---

### Workflow 3: Quality Control Review (QC)

1. **Login** to QC dashboard
2. View **"Pending QC"** queue
3. **Select a Report** to review
4. **Review All Sections**:
   - Applicant information
   - Verification details
   - Supporting documents
   - Analyst findings
   - Conclusions
5. **Check for**:
   - Completeness
   - Accuracy
   - Supporting evidence
   - Proper formatting
   - Clear recommendations
6. **Decision**:
   - **Approve**: If report meets quality standards
   - **Reject**: If revisions are needed
7. **Provide Feedback** (if rejecting):
   - Specify issues found
   - Suggest improvements
   - Set priority for revision
8. **Approve Final Report**
9. Report moves to **"Completed"** status

**Tips**:
- Use the QC checklist
- Provide clear, constructive feedback
- Check all uploaded documents
- Verify data accuracy
- Maintain consistency in standards

---

### Workflow 4: Bulk Upload (Client Admin)

1. **Login** to client admin portal
2. Navigate to **"Bulk Upload"**
3. **Download Template**:
   - Excel/CSV template with required fields
4. **Fill Template**:
   - Add all applicant details
   - Ensure data format is correct
   - Include all mandatory fields
5. **Upload File**:
   - Select filled template
   - Click "Upload"
6. **Review Validation**:
   - Check for errors
   - Fix any validation issues
   - Re-upload if needed
7. **Confirm Upload**:
   - Review summary
   - Confirm batch submission
8. **Track Progress**:
   - Monitor bulk upload status
   - View individual case progress

**Tips**:
- Validate data before upload
- Use the provided template exactly
- Check for duplicate entries
- Keep backup of your upload file
- Large batches may take time to process

---

### Workflow 5: Report Generation and Download

**For Clients**:
1. **Login** to client portal
2. Navigate to **"My Verifications"** or **"Reports"**
3. **Search/Filter**:
   - By date range
   - By status
   - By reference number
4. **View Details** of completed verification
5. **Download Report**:
   - PDF format
   - Certificate (if applicable)
6. **Print** or **Save** for records

**For Internal Users**:
1. Access **"Reports"** module
2. Select **Report Type**:
   - Daily Analyst Report
   - Case Status Report
   - Monthly Summary
   - Client-wise Report
3. **Apply Filters**:
   - Date range
   - Client
   - Status
   - Analyst
4. **Generate Report**
5. **Export** (Excel, PDF, CSV)

---

## Features by Role

### Document Upload

**Who Can Upload**:
- Client Users (applicant documents)
- Analysts (verification evidence)
- All internal users (supporting documents)

**Allowed File Types**:
- PDF, DOC, DOCX
- JPG, PNG, GIF
- Maximum size: 5 MB per file

**Upload Process**:
1. Click "Upload Document" or "Add File"
2. Select file from your computer
3. Choose document type (if prompted)
4. Add description (optional)
5. Click "Upload"
6. Wait for confirmation

---

### Status Tracking

**Verification Statuses**:
1. **Submitted**: Verification request received
2. **Assigned**: Assigned to an analyst
3. **In Progress**: Analyst working on the case
4. **Insufficient**: Additional information needed
5. **QC Review**: Under quality control review
6. **QC Rejected**: Sent back to analyst for revision
7. **Completed**: Verification completed successfully
8. **Closed**: Case finalized and archived
9. **Cancelled**: Verification cancelled

**Tracking Your Cases**:
- Real-time status updates
- Timeline view of progress
- Email notifications on status change
- Expected completion date (TAT)
- Current stage indicator

---

### Communication Tools

**Internal Communication**:
- Case notes and comments
- Analyst-to-analyst messaging
- Team lead notifications
- System alerts

**External Communication**:
- Email templates for verification requests
- Client notifications
- Insufficient information requests
- Completion notifications

---

### Search and Filtering

**Search Criteria**:
- Verification ID
- Client Reference Number
- Applicant Name
- Date Range
- Status
- Check Type
- Assigned Analyst
- Company/Client

**Advanced Search**:
- Multiple filter combination
- Custom date ranges
- Saved search criteria
- Export search results

---

### Dashboard Analytics

**Available Metrics**:
- Total verifications (by status)
- Pending cases
- Overdue cases
- Completion rate
- Average TAT
- Analyst performance
- Client activity

**Visualizations**:
- Bar charts
- Pie charts
- Line graphs (trends)
- Tables with sorting

---

## Frequently Asked Questions

### General Questions

**Q: How do I reset my password?**  
A: Contact your administrator or use the "Forgot Password" link on the login page.

**Q: What is the turnaround time (TAT) for verifications?**  
A: Standard TAT is 10 business days, but can vary based on check type and complexity.

**Q: Can I track multiple verifications at once?**  
A: Yes, use the dashboard or "My Verifications" page to view all your cases.

**Q: What file formats are accepted for uploads?**  
A: PDF, DOC, DOCX, JPG, PNG, GIF (maximum 5 MB per file).

---

### Client Questions

**Q: How do I submit a new verification request?**  
A: Login → Navigate to "New Verification" → Fill form → Upload documents → Submit.

**Q: Can I upload multiple applicants at once?**  
A: Yes, Client Admins can use the bulk upload feature with the provided Excel template.

**Q: How will I be notified when verification is complete?**  
A: You'll receive an email notification and can also check the dashboard for status updates.

**Q: Can I cancel a verification request?**  
A: Contact support with the verification ID to cancel. Cancellation may not be possible if work has already started.

**Q: How do I download completed reports?**  
A: Go to "My Verifications" → Select completed case → Click "Download Report".

---

### Analyst Questions

**Q: How are cases assigned to me?**  
A: Cases are assigned by Team Leads or automatically based on workload and expertise.

**Q: What should I do if I can't reach the employer/institution?**  
A: Document all contact attempts and escalate to your Team Lead after 3-4 attempts.

**Q: How do I request additional information from the client?**  
A: Change status to "Insufficient" and use the "Request Info" button to send a notification.

**Q: Can I reassign a case if I'm unable to complete it?**  
A: No, contact your Team Lead to reassign the case.

**Q: What if I need more time to complete a verification?**  
A: Inform your Team Lead immediately to request a TAT extension.

---

### Technical Questions

**Q: Why can't I upload a document?**  
A: Check file size (max 5 MB), file type (PDF, DOC, DOCX, JPG, PNG), and your internet connection.

**Q: The page is not loading. What should I do?**  
A: Try refreshing the page, clearing your browser cache, or using a different browser.

**Q: Can I use the system on mobile devices?**  
A: Yes, the system is accessible on mobile browsers, but desktop is recommended for full functionality.

**Q: I'm getting an error message. What should I do?**  
A: Take a screenshot of the error and contact support at support@backcheckgroup.com.

---

## Tips for Effective Use

### For Clients
1. **Provide Complete Information**: More details = faster verification
2. **Use Clear Document Scans**: Ensure documents are legible
3. **Maintain Consistent References**: Use systematic reference numbering
4. **Check Status Regularly**: Stay updated on progress
5. **Respond Promptly**: Reply quickly to insufficient information requests

### For Analysts
1. **Organize Your Workflow**: Prioritize by TAT and complexity
2. **Document Everything**: Keep detailed notes of all activities
3. **Communicate Proactively**: Update Team Lead on challenges
4. **Quality Over Speed**: Accuracy is more important than rushing
5. **Use Templates**: Leverage email and report templates

### For Team Leads
1. **Balance Workload**: Distribute cases evenly among analysts
2. **Monitor TAT**: Keep track of deadlines
3. **Provide Feedback**: Regular feedback improves quality
4. **Support Your Team**: Be available for questions and escalations
5. **Review Trends**: Identify patterns and optimize processes

---

## Getting Help

### Support Channels
- **Email**: support@backcheckgroup.com
- **Phone**: +92-21-32863920-31
- **Live Chat**: Available during business hours
- **Help Desk**: Submit ticket through the system

### Support Hours
- **Monday - Friday**: 9:00 AM - 6:00 PM (PKT)
- **Saturday**: 9:00 AM - 2:00 PM (PKT)
- **Sunday**: Closed
- **Emergency Support**: Available for critical issues

### What to Include in Support Requests
1. Your username and company (if applicable)
2. Verification ID (if applicable)
3. Detailed description of the issue
4. Screenshots (if relevant)
5. Steps to reproduce the problem
6. Browser and operating system information

---

**Last Updated**: 2026  
**Version**: 3.4  
**Maintained by**: Background Check Support Team
