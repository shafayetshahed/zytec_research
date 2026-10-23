# Portfolio: method and items for owner review

## Sources

The private workbooks were found at `Documents/Work/Ag/` (not in the project):

| File | Sheets | Period |
| --- | --- | --- |
| `2023_Work and Bill.xlsx` | Dessertation, 23-November, 23-December, 24-January, 24-March, 25-April, 25-May, June-24, 24-Aug, Sep-24, Nov-24 | Nov 2023 – Nov 2024 |
| `2025_Work and Bill.xlsx` | 2025_March, 2025_June, Dissertation, November | 2025 |
| `2026_Work and Bill.xlsx` | 2026_Feb, 2026_May | 2026 |

626 rows with a work title (391 distinct titles) were read. **Only** the work title, the sheet (for the year) and an explicit country value were extracted. Student names, writer names, words, fees, payment/due amounts, deadlines and notes were not copied into the project. The spreadsheets are not in the repository or the public folder (a test checks that no `.xlsx` exists under `public/`).

## Rules applied

- Generic labels ("Week 5 work 3", "New Batch 1", "3 copies of work", "Adjusted Due", "All Briefs Chat") were dropped.
- "Copy 1–5" duplicate entries were merged into a single module entry.
- **Exam entries were excluded** (e.g. "Exam", "Exam-Hospitality Tool Kit", the exam part of "Managing Accommodation Services Exam + Report") because the site states that the business does not sit exams.
- Country is shown only where a row says so: the `Country` column in `2026_May` (AUS, UK) or "Uk"/"Australia" in the title. Two rows marked "BDT" (a currency, not a country) were ignored. **No row records Canada or the USA**, so the site does not claim work in those countries.
- Institutions were **not** inferred from module codes, and no institution names appear on the public site.
- Codes recorded without a title are published as "Title not recorded".

## Items for the owner to confirm

1. **MBIS402 / MBIS403 titles.** The records give "Programming Fundamentals and Techniques" against both codes, and "Data Modelling and Database Development" against MBIS403. Shown as "title to be confirmed".
2. **BMAF010 "Software Development"** appears in a combined row with CPU3003-20; confirm the pairing.
3. **"AEDEL411"** appears in a TAE row and is probably a typo for TAEDEL411; it was left out.
4. **TAE unit titles** (TAEDES411, TAEDES412, TAEPDD401, TAELLN421, TAELLN423, TAEDEL311, TAEDEL412) are national Australian VET units; titles can be confirmed on training.gov.au and added.
5. **Dissertation year.** The 2023 workbook's "Dessertation" sheet is undated, so only the 2025 dissertation sheet's year is shown.
6. **Logo wording**: the logo reads "Zytec RESEARCH"; the business name is "Zytecs Research".
7. **Institutions named in the brief** (University of Birmingham, Ulster University, Glasgow Caledonian University, University of Toronto, New York University, "CIM Australia", "All Star Canada"): none are named on the site, to avoid implying affiliation. "CIM Australia" and "All Star Canada" could not be matched to a single institution without more information. If you want to mention institutions, add a line such as "students we have supported have studied at…" only for institutions supported by your own records, and never use their logos.
8. **Integrity alignment**: the records include entries (multiple copies of one assignment, exam work) that would not fit the "guidance, not ghost-writing" positioning the site now states publicly. The site's commitments in `/academic-integrity`, the services' "Limitations" and the FAQs need to match how the business operates from launch.

## Case-study drafts

No public case studies have been created: the records contain no outcome, permission or narrative detail, and inventing them is not acceptable. A case study needs, from the owner: written student permission, the module and task type, what support was given (in learning-support terms), and an outcome the student is happy to share. A template:

> **Module area:** e.g. Information systems (MBIS406, Australia, 2026)
> **Challenge:** what the student found difficult (in their words, with permission)
> **Support provided:** e.g. two guidance sessions on data-visualisation choices and written feedback on a draft
> **Result:** only what the student confirms and agrees to publish

Add approved entries to `case_studies` in `resources/content/portfolio.php`.
