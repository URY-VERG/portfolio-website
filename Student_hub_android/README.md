# Student Hub Android App

Native Android UI project generated from your `Student_hub` PHP website.

## Implemented Screens

- Auth:
  - `MainActivity` (role chooser)
  - `LoginActivity` (student)
  - `RegisterActivity`
  - `StaffLoginActivity`
- Student pages:
  - `StudentDashboardActivity`
  - `StudentNotesActivity` (RecyclerView)
  - `StudentLeaveActivity` (form + RecyclerView)
  - `StudentNoticesActivity` (RecyclerView)
  - `StudentExamsActivity` (RecyclerView)
  - `StudentToolsActivity` (SGPA -> percentage)
  - `StudentProfileActivity`
- Staff pages:
  - `StaffDashboardActivity`
  - `StaffNotesActivity` (RecyclerView)
  - `StaffLeaveActivity` (RecyclerView)
  - `StaffNoticesActivity` (RecyclerView)
  - `StaffExamsActivity` (RecyclerView)

## Material / UI

- Material 3 theme
- Card-based layout
- Professional spacing and typography
- RecyclerView list cards for notes, notices, leaves, exams

## API Layer

- Retrofit is added:
  - `ApiClient.kt`
  - `ApiService.kt`
- Update `BASE_URL` in `ApiClient.kt`:
  - `https://your-domain-or-ip/Student_hub/`

> Important: your current PHP endpoints mostly return HTML.  
> For clean Android integration, expose JSON APIs (recommended new files like `api/login.php`, `api/notices.php`, etc.).

## Run Steps (Android Studio)

1. Open Android Studio.
2. Click **Open** and select:
   - `Student_hub_android`
3. Let Gradle sync.
4. Use emulator or USB device.
5. Click **Run app**.

## Connect With Existing PHP Backend

1. Host your existing `Student_hub` folder using:
   - XAMPP / WAMP / local Apache / cloud hosting.
2. Ensure Android can reach backend URL from device.
3. Set base URL in `ApiClient.kt`.
4. Convert each required endpoint output to JSON.
5. Replace sample list data in activities with API calls.

## Suggested Next Upgrade

- Add repository + ViewModel per module
- Use session/token persistence with DataStore
- Add file upload API for notes/profile image
- Add exam attempt submission API with score history
