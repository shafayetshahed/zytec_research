<?php

/*
|--------------------------------------------------------------------------
| Anonymised record of work
|--------------------------------------------------------------------------
|
| Derived from the owner's private "Work and Bill" spreadsheets (2023, 2025
| and 2026 workbooks, covering November 2023 to May 2026). Only module codes,
| module titles as written in the records, broad task types, the year(s)
| recorded and an explicitly recorded country are used.
|
| Deliberately excluded: student and writer names, fees and payment status,
| deadlines, internal notes, duplicate "copy" entries, exam entries and
| generic labels such as "Week 5 work".
|
| title_status:
|   recorded       title appears in the records alongside the code
|   not_recorded   only the code was recorded; no title is published
|   to_confirm     the records give inconsistent titles; owner to confirm
|
| countries: only where a row explicitly records it ("AUS" column value or
| "Uk"/"Australia" in the entry). Never inferred from a module code.
|
*/

return [

    'areas' => [
        'business' => 'Business and management',
        'marketing' => 'Marketing and consumer behaviour',
        'hrm' => 'Human resource management',
        'finance' => 'Accounting and finance',
        'it' => 'IT, computing and information systems',
        'engineering' => 'Engineering and technical',
        'hospitality' => 'Hospitality, tourism and events',
        'law' => 'Law',
        'education' => 'Education and training',
        'research' => 'Research and academic skills',
    ],

    'entries' => [
        // Business and management
        ['area' => 'business', 'code' => 'MGMT401', 'title' => 'Contemporary Management and Leadership', 'title_status' => 'recorded', 'tasks' => ['Assessment tasks', 'Group work'], 'years' => [2025], 'countries' => []],
        ['area' => 'business', 'code' => 'MGMT402', 'title' => 'Strategic Management', 'title_status' => 'recorded', 'tasks' => ['Case analysis report', 'Individual strategic analysis report'], 'years' => [2025, 2026], 'countries' => ['Australia']],
        ['area' => 'business', 'code' => 'BSS050-6', 'title' => 'Strategic Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'Strategic Management (simulation and report)', 'title_status' => 'recorded', 'tasks' => ['Business simulation', 'Report'], 'years' => [2024], 'countries' => []],
        ['area' => 'business', 'code' => 'BUS607', 'title' => 'Business Model Analysis', 'title_status' => 'recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => []],
        ['area' => 'business', 'code' => 'CGES401', 'title' => 'Corporate Governance, Ethics and Sustainability', 'title_status' => 'recorded', 'tasks' => ['Assessment tasks'], 'years' => [2025, 2026], 'countries' => []],
        ['area' => 'business', 'code' => 'MAR038-6', 'title' => 'Intercultural Business Competencies', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'business', 'code' => 'BMA4000', 'title' => 'Business Environment', 'title_status' => 'recorded', 'tasks' => ['Group presentation'], 'years' => [2025], 'countries' => []],
        ['area' => 'business', 'code' => 'PMT701', 'title' => 'Agile Project Management', 'title_status' => 'recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'Project Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'Operations Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'Global Strategy and Business Strategy', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'Examination of Entrepreneurial Opportunities', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'International Entrepreneurial Ecosystems', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'Industry 4.0', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2023], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'Technological Innovation and Change Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'Management Practice', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'Consultancy project', 'title_status' => 'recorded', 'tasks' => ['Consultancy report'], 'years' => [2023], 'countries' => []],
        ['area' => 'business', 'code' => null, 'title' => 'Leading People in Creative Teams', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2026], 'countries' => []],
        ['area' => 'business', 'code' => 'STA101', 'title' => 'Data Driven Decision Making', 'title_status' => 'recorded', 'tasks' => ['Assessment tasks'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'business', 'code' => 'BUS201', 'title' => 'Creativity and Innovation for Business', 'title_status' => 'recorded', 'tasks' => ['Presentation'], 'years' => [2026], 'countries' => []],
        ['area' => 'business', 'code' => 'BUS102', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'business', 'code' => 'BUS1002', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'business', 'code' => 'MGN301', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'business', 'code' => 'MGN302', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'business', 'code' => 'HC1052', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'business', 'code' => 'HB2027', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => ['Australia']],

        // Marketing and consumer behaviour
        ['area' => 'marketing', 'code' => 'MGMT403', 'title' => 'Marketing Management and Research Methods', 'title_status' => 'recorded', 'tasks' => ['Assessment tasks', 'Marketing plan', 'Group work'], 'years' => [2025, 2026], 'countries' => ['Australia']],
        ['area' => 'marketing', 'code' => 'MAR036-6', 'title' => 'Brand Communication and Reputation Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'marketing', 'code' => 'MKT102', 'title' => 'Consumer Behaviour', 'title_status' => 'recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => []],
        ['area' => 'marketing', 'code' => 'MKT101', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'marketing', 'code' => null, 'title' => 'Digital Marketing', 'title_status' => 'recorded', 'tasks' => ['Assignment', 'Reflection'], 'years' => [2023, 2026], 'countries' => []],
        ['area' => 'marketing', 'code' => null, 'title' => 'Global Marketing and Sales Development', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2023], 'countries' => []],
        ['area' => 'marketing', 'code' => null, 'title' => 'Strategy and Brand Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2023], 'countries' => []],
        ['area' => 'marketing', 'code' => null, 'title' => 'Advertising Campaign', 'title_status' => 'recorded', 'tasks' => ['Campaign report'], 'years' => [2026], 'countries' => ['Australia']],

        // Human resource management
        ['area' => 'hrm', 'code' => 'SHR080-6', 'title' => 'Talent Resourcing and Development', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'hrm', 'code' => null, 'title' => 'International Human Resource Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2023, 2024], 'countries' => []],
        ['area' => 'hrm', 'code' => null, 'title' => 'Human Resource Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2023], 'countries' => []],
        ['area' => 'hrm', 'code' => null, 'title' => 'HRM and the Business Context', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'hrm', 'code' => null, 'title' => 'Skills for Managers', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2024], 'countries' => []],
        ['area' => 'hrm', 'code' => null, 'title' => 'People Skills', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2024], 'countries' => []],
        ['area' => 'hrm', 'code' => null, 'title' => 'Managing People', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2023], 'countries' => []],

        // Accounting and finance
        ['area' => 'finance', 'code' => 'AAF044-6', 'title' => 'Accounting and Finance Business Report', 'title_status' => 'recorded', 'tasks' => ['Business report'], 'years' => [2024], 'countries' => []],
        ['area' => 'finance', 'code' => 'FIN101', 'title' => 'Fundamentals of Financial Literacy', 'title_status' => 'recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => []],
        ['area' => 'finance', 'code' => null, 'title' => 'Financial Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2023], 'countries' => []],
        ['area' => 'finance', 'code' => null, 'title' => 'Strategic Financial Management', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2023], 'countries' => []],
        ['area' => 'finance', 'code' => null, 'title' => 'Strategic and Financial Performance Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2023], 'countries' => []],
        ['area' => 'finance', 'code' => null, 'title' => 'Finance and Accounting for Business', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],

        // IT, computing and information systems
        ['area' => 'it', 'code' => 'MBIS401', 'title' => 'Information Systems and Strategy', 'title_status' => 'recorded', 'tasks' => ['Case study', 'Assessment tasks'], 'years' => [2024, 2025], 'countries' => []],
        ['area' => 'it', 'code' => 'MBIS402', 'title' => 'Programming Fundamentals and Techniques', 'title_status' => 'to_confirm', 'tasks' => ['Group project', 'Weekly activities'], 'years' => [2025, 2026], 'countries' => ['Australia']],
        ['area' => 'it', 'code' => 'MBIS403', 'title' => 'Data Modelling and Database Development', 'title_status' => 'to_confirm', 'tasks' => ['Assessment tasks', 'Weekly activities'], 'years' => [2025, 2026], 'countries' => ['Australia']],
        ['area' => 'it', 'code' => 'MBIS404', 'title' => 'Networks and Communication', 'title_status' => 'recorded', 'tasks' => ['Group case study', 'Weekly activities'], 'years' => [2024, 2025, 2026], 'countries' => ['Australia']],
        ['area' => 'it', 'code' => 'MBIS405', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task', 'Weekly activities'], 'years' => [2025, 2026], 'countries' => ['Australia']],
        ['area' => 'it', 'code' => 'MBIS406', 'title' => 'Big Data Analytics', 'title_status' => 'recorded', 'tasks' => ['Assessment tasks'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'it', 'code' => 'MSIT401', 'title' => 'System Development Methodologies', 'title_status' => 'recorded', 'tasks' => ['Assessment tasks', 'Reflective journal'], 'years' => [2024, 2025], 'countries' => []],
        ['area' => 'it', 'code' => 'MSIT402', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Group presentation', 'Written assessment', 'Weekly activities'], 'years' => [2025, 2026], 'countries' => ['Australia']],
        ['area' => 'it', 'code' => 'MSIT403', 'title' => 'Project and Change Management in ICT', 'title_status' => 'recorded', 'tasks' => ['Assessment tasks', 'Weekly activities'], 'years' => [2025, 2026], 'countries' => ['Australia']],
        ['area' => 'it', 'code' => 'MSIT404', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task', 'Weekly activities'], 'years' => [2025, 2026], 'countries' => []],
        ['area' => 'it', 'code' => 'MSIT405', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Reflective journal'], 'years' => [2026], 'countries' => []],
        ['area' => 'it', 'code' => 'BMA4003', 'title' => 'Managing Data', 'title_status' => 'recorded', 'tasks' => ['Assessment task'], 'years' => [2025], 'countries' => []],
        ['area' => 'it', 'code' => 'BUS707', 'title' => 'System Design for Digital Transformation', 'title_status' => 'recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => []],
        ['area' => 'it', 'code' => 'CPU3003-20', 'title' => 'Computational Reasoning', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2026], 'countries' => ['United Kingdom']],
        ['area' => 'it', 'code' => 'BMAF010', 'title' => 'Software Development', 'title_status' => 'to_confirm', 'tasks' => ['Coursework'], 'years' => [2026], 'countries' => ['United Kingdom']],
        ['area' => 'it', 'code' => 'BISY1003', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Portfolio tasks'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'it', 'code' => 'ICTICT532', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task'], 'years' => [2025], 'countries' => []],
        ['area' => 'it', 'code' => null, 'title' => 'Data Visualisation', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'it', 'code' => null, 'title' => 'Database, ERD, Python and WordPress practical activities', 'title_status' => 'recorded', 'tasks' => ['Weekly practical activities'], 'years' => [2025], 'countries' => []],
        ['area' => 'it', 'code' => null, 'title' => 'Digital Transformation', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2026], 'countries' => []],

        // Engineering and technical
        ['area' => 'engineering', 'code' => 'EGR9019', 'title' => 'Applied Thermo-fluids Systems', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2024], 'countries' => []],
        ['area' => 'engineering', 'code' => 'EGR9028', 'title' => 'Laser Materials Processing', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2024], 'countries' => []],
        ['area' => 'engineering', 'code' => 'EGR9044', 'title' => 'Advanced Modelling and Simulation', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2024], 'countries' => []],
        ['area' => 'engineering', 'code' => 'EGR9045', 'title' => 'Research Methods and Engineering Innovation', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2024], 'countries' => []],
        ['area' => 'engineering', 'code' => null, 'title' => 'Power Generation Technologies', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2024], 'countries' => []],
        ['area' => 'engineering', 'code' => null, 'title' => 'Sustainable Energy Systems and Climate Change', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2024], 'countries' => []],
        ['area' => 'engineering', 'code' => null, 'title' => 'Engineering Management', 'title_status' => 'recorded', 'tasks' => ['Case study'], 'years' => [2023], 'countries' => []],
        ['area' => 'engineering', 'code' => null, 'title' => 'Engineering Operations Management', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2024], 'countries' => []],

        // Hospitality, tourism and events
        ['area' => 'hospitality', 'code' => 'BAM6059', 'title' => 'Events Management', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2025], 'countries' => []],
        ['area' => 'hospitality', 'code' => 'BAM6062', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Coursework and project report'], 'years' => [2025], 'countries' => ['United Kingdom']],
        ['area' => 'hospitality', 'code' => 'BAM6060', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Report', 'Presentation'], 'years' => [2026], 'countries' => []],
        ['area' => 'hospitality', 'code' => 'Unit 39', 'title' => 'Tourist Resort Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2025], 'countries' => []],
        ['area' => 'hospitality', 'code' => 'Unit 41', 'title' => 'Hospitality Business Strategy', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2025], 'countries' => []],
        ['area' => 'hospitality', 'code' => null, 'title' => 'Managing Accommodation Services', 'title_status' => 'recorded', 'tasks' => ['Report'], 'years' => [2024], 'countries' => []],
        ['area' => 'hospitality', 'code' => null, 'title' => 'Managing Food Production', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'hospitality', 'code' => null, 'title' => 'Hospitality Marketing Essentials', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'hospitality', 'code' => null, 'title' => 'Hospitality Toolkit', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2024], 'countries' => []],
        ['area' => 'hospitality', 'code' => null, 'title' => 'Hospitality Management', 'title_status' => 'recorded', 'tasks' => ['Assignment'], 'years' => [2023], 'countries' => []],
        ['area' => 'hospitality', 'code' => 'HOS303', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'hospitality', 'code' => 'HOS304', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assessment task'], 'years' => [2026], 'countries' => ['Australia']],

        // Law
        ['area' => 'law', 'code' => 'LAW4003', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Legal memorandum'], 'years' => [2026], 'countries' => ['United Kingdom']],
        ['area' => 'law', 'code' => 'LAW4001', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Coursework'], 'years' => [2026], 'countries' => ['United Kingdom']],
        ['area' => 'law', 'code' => 'LAW4010', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Coursework'], 'years' => [2026], 'countries' => ['United Kingdom']],
        ['area' => 'law', 'code' => 'LAW4000-20', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assignment'], 'years' => [2025], 'countries' => []],
        ['area' => 'law', 'code' => 'LAW4004-20', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assignment'], 'years' => [2025], 'countries' => []],
        ['area' => 'law', 'code' => 'LAW4005-20', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Assignment'], 'years' => [2025], 'countries' => []],

        // Education and training (Australian VET unit codes; titles to be verified on training.gov.au before adding)
        ['area' => 'education', 'code' => 'TAEDES411', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Unit assessment'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'education', 'code' => 'TAEDES412', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Unit assessment'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'education', 'code' => 'TAEPDD401', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Unit assessment'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'education', 'code' => 'TAELLN421', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Unit assessment'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'education', 'code' => 'TAELLN423', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Unit assessment'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'education', 'code' => 'TAEDEL311', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Unit assessment'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'education', 'code' => 'TAEDEL412', 'title' => null, 'title_status' => 'not_recorded', 'tasks' => ['Unit assessment'], 'years' => [2026], 'countries' => ['Australia']],

        // Research and academic skills
        ['area' => 'research', 'code' => null, 'title' => 'Research proposals', 'title_status' => 'recorded', 'tasks' => ['Research proposal'], 'years' => [2023, 2024, 2026], 'countries' => ['United Kingdom']],
        ['area' => 'research', 'code' => null, 'title' => 'Research methodology', 'title_status' => 'recorded', 'tasks' => ['Methodology'], 'years' => [2024], 'countries' => []],
        ['area' => 'research', 'code' => null, 'title' => 'Journal article reviews', 'title_status' => 'recorded', 'tasks' => ['Critical review'], 'years' => [2024], 'countries' => []],
        ['area' => 'research', 'code' => null, 'title' => 'Dissertations', 'title_status' => 'recorded', 'tasks' => ['Dissertation'], 'years' => [2025], 'countries' => []],
        ['area' => 'research', 'code' => null, 'title' => 'Capstone project', 'title_status' => 'recorded', 'tasks' => ['Capstone'], 'years' => [2026], 'countries' => ['Australia']],
        ['area' => 'research', 'code' => null, 'title' => 'Reflective journals and reflections', 'title_status' => 'recorded', 'tasks' => ['Reflective writing'], 'years' => [2023, 2025, 2026], 'countries' => []],
        ['area' => 'research', 'code' => 'BMAF001-20', 'title' => 'Communication Skills', 'title_status' => 'recorded', 'tasks' => ['Coursework'], 'years' => [2025], 'countries' => []],
    ],

    /*
    | Approved public case studies. None have been approved yet; drafts for
    | owner review live in docs/PORTFOLIO-DRAFTS.md.
    */
    'case_studies' => [],

];
