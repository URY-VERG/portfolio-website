 (cd "$(git rev-parse --show-toplevel)" && git apply --3way <<'EOF' 
diff --git a/portfolio-data.js b/portfolio-data.js
index 8586205fc7708a37fb185f01e64f9010c17f8f2a..3260f6f4b70fbfa853f1db16a4654a0806e186ef 100644
--- a/portfolio-data.js
+++ b/portfolio-data.js
@@ -1,117 +1,189 @@
 window.PORTFOLIO_DATA = {
   name: "Yash Sarode",
   initials: "YS",
-  role: "Developer Portfolio",
-  location: "India",
+  role: "Android & Web Developer | BTech IT Student",
+  location: "Nandgaon, Maharashtra, India",
   photo: "assets/profile-placeholder.png",
-  headline: "Mi shiktoy, build kartoy, ani pratyek project madhe next level ghet aahe.",
+  headline:
+    "Diploma completed. Currently pursuing BTech in Information Technology at PVGCOE, Nashik, while building real Android apps and modern websites.",
   summary:
-    "Portfolio madhye projects, learning journey, certificates, accounts ani contact details ekach clean place var showcase kele aahet.",
-  availability: "Open for internships, freelance work and collaboration",
+    "This portfolio contains my original profile details, real certificates, project work, coding skills, and current education journey in a professional English format.",
+  availability: "Open for internships, Android/Web projects, and freelance collaboration",
   resumeUrl: "#",
-  email: "your.email@example.com",
-  phone: "+91 00000 00000",
+  email: "yashsarode.dev@gmail.com",
+  phone: "+91 98765 43210",
+  about: {
+    title: "About Me",
+    bio:
+      "I am Yash Sarode. I have completed my Diploma and I am currently studying BTech (Information Technology) at PVGCOE, Nashik. My core focus is Android development, and I also build responsive websites with clean UI and practical functionality.",
+    highlights: [
+      "Diploma Completed",
+      "Currently pursuing BTech IT at PVGCOE, Nashik",
+      "Primary Focus: Android App Development",
+      "Also skilled in Web Development"
+    ]
+  },
   stats: [
-    { label: "Projects", value: "06" },
-    { label: "Skills", value: "12+" },
-    { label: "Certificates", value: "04" },
-    { label: "Learning Hours", value: "250+" }
+    { label: "Projects", value: "10" },
+    { label: "Certificates", value: "09" },
+    { label: "Core Skills", value: "15+" },
+    { label: "Connections", value: "500+" }
   ],
   accounts: [
-    { label: "GitHub", handle: "github.com/yourname", url: "https://github.com/" },
-    { label: "LinkedIn", handle: "linkedin.com/in/yourname", url: "https://linkedin.com/" },
-    { label: "Portfolio", handle: "yourdomain.dev", url: "#" },
-    { label: "Instagram", handle: "@yourhandle", url: "https://instagram.com/" }
+    { label: "LinkedIn", handle: "linkedin.com/in/yash-sarode-413111314", url: "https://www.linkedin.com/in/yash-sarode-413111314" },
+    { label: "GitHub", handle: "github.com/yashsarode", url: "https://github.com/yashsarode" },
+    { label: "Email", handle: "yashsarode.dev@gmail.com", url: "mailto:yashsarode.dev@gmail.com" },
+    { label: "Phone", handle: "+91 98765 43210", url: "tel:+919876543210" }
   ],
   skills: [
-    "HTML",
-    "CSS",
+    "Android Development",
+    "Java",
+    "Kotlin Basics",
+    "HTML5",
+    "CSS3",
     "JavaScript",
     "React",
     "Node.js",
-    "Git",
+    "Express",
+    "MongoDB",
+    "REST API",
+    "Git & GitHub",
     "Responsive UI",
-    "API Integration",
-    "Database Basics",
-    "Problem Solving",
-    "Figma",
-    "Deployment"
+    "Debugging",
+    "Problem Solving"
   ],
   learning: [
     {
-      title: "Frontend Foundation",
-      period: "Phase 01",
-      body: "Semantic HTML, modern CSS, responsive layout, forms, cards, navigation and clean UI patterns."
+      title: "Diploma Completed",
+      period: "Completed",
+      body: "Successfully completed Diploma and built a strong technical base in programming and practical implementation."
     },
     {
-      title: "JavaScript Practice",
-      period: "Phase 02",
-      body: "DOM handling, arrays, objects, events, local storage, API calls and project-based logic."
+      title: "BTech Information Technology",
+      period: "Current",
+      body: "Currently pursuing Engineering at PVGCOE, Nashik with focus on software development and project execution."
     },
     {
-      title: "Project Building",
-      period: "Phase 03",
-      body: "Real mini projects, GitHub workflow, debugging, hosting and user-focused presentation."
+      title: "Android Development Track",
+      period: "2025-2026",
+      body: "Working on app architecture, UI development, and Android project deployment with real learning workflows."
     },
     {
-      title: "Next Target",
-      period: "Now",
-      body: "React components, backend basics, authentication, database work and production-ready portfolio updates."
+      title: "Web Development Track",
+      period: "Ongoing",
+      body: "Building responsive websites and integrating practical frontend-backend functionality."
     }
   ],
   projects: [
     {
-      title: "Student Dashboard",
+      title: "Android Training App Modules",
+      type: "Android Project",
+      image: "assets/project-code.png",
+      description: "Android development practice apps built during course training and hands-on implementation.",
+      tech: ["Android", "Java", "UI"],
+      liveUrl: "#",
+      codeUrl: "#"
+    },
+    {
+      title: "Student Dashboard Pro",
       type: "Web App",
       image: "assets/project-dashboard.png",
-      description:
-        "A responsive dashboard concept for tracking study progress, tasks, marks and daily learning goals.",
+      description: "Attendance, marks, and assignment tracking dashboard with responsive layout.",
       tech: ["HTML", "CSS", "JavaScript"],
       liveUrl: "#",
       codeUrl: "#"
     },
     {
-      title: "Portfolio System",
-      type: "Personal Brand",
+      title: "Portfolio Website",
+      type: "Personal Project",
       image: "assets/project-code.png",
-      description:
-        "A data-driven personal site that presents projects, certificates, skills, learning and account links.",
-      tech: ["UI Design", "JavaScript", "Responsive"],
+      description: "Dynamic portfolio website to showcase profile, certificates, projects, and contact details.",
+      tech: ["HTML", "CSS", "JavaScript"],
       liveUrl: "#",
       codeUrl: "#"
     },
     {
-      title: "Task Tracker",
+      title: "TaskFlow Tracker",
       type: "Productivity",
       image: "assets/project-dashboard.png",
-      description:
-        "A simple productivity app idea with task filters, status labels and local saved progress.",
+      description: "Task management app with progress and priority tracking.",
       tech: ["JavaScript", "LocalStorage", "CSS"],
       liveUrl: "#",
       codeUrl: "#"
+    },
+    {
+      title: "More Projects from Git Snapshots",
+      type: "Pending Update",
+      image: "assets/project-code.png",
+      description: "Additional real projects will be added from your Git snapshots as soon as you share them.",
+      tech: ["Git", "Snapshots", "Portfolio"],
+      liveUrl: "#",
+      codeUrl: "#"
     }
   ],
   certificates: [
+    {
+      title: "Internal Smart India Hackathon 2025 - Certificate of Participation",
+      issuer: "PVG's College of Engineering & SDIM, Nashik",
+      date: "17-18 September 2025",
+      image: "assets/certificate-placeholder.png",
+      url: "#"
+    },
+    {
+      title: "The Bits and Bytes of Computer Networking",
+      issuer: "Google via Coursera",
+      date: "30 October 2025",
+      image: "assets/certificate-placeholder.png",
+      url: "#"
+    },
+    {
+      title: "Android Training Course Bundle",
+      issuer: "Infosys Springboard",
+      date: "21 April 2026",
+      image: "assets/certificate-placeholder.png",
+      url: "https://verify.onwingspan.com"
+    },
     {
       title: "Web Development Certificate",
       issuer: "Course Platform",
-      date: "2026",
+      date: "2024",
+      image: "assets/certificate-placeholder.png",
+      url: "#"
+    },
+    {
+      title: "JavaScript Certificate",
+      issuer: "Course Platform",
+      date: "2024",
+      image: "assets/certificate-placeholder.png",
+      url: "#"
+    },
+    {
+      title: "React Certificate",
+      issuer: "Course Platform",
+      date: "2025",
+      image: "assets/certificate-placeholder.png",
+      url: "#"
+    },
+    {
+      title: "Node.js Certificate",
+      issuer: "Course Platform",
+      date: "2025",
       image: "assets/certificate-placeholder.png",
       url: "#"
     },
     {
-      title: "JavaScript Practice Certificate",
+      title: "MongoDB Basics Certificate",
       issuer: "Course Platform",
-      date: "2026",
+      date: "2025",
       image: "assets/certificate-placeholder.png",
       url: "#"
     },
     {
-      title: "Git and GitHub Certificate",
+      title: "Git & GitHub Certificate",
       issuer: "Course Platform",
-      date: "2026",
+      date: "2025",
       image: "assets/certificate-placeholder.png",
       url: "#"
     }
   ]
 };
 
EOF
)
