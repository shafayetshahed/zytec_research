<?php

/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
|
| Every service is framed as guidance, feedback and learning support. The
| owner must confirm each entry reflects a service the business genuinely
| provides before launch (see docs/ADMIN-GUIDE.md, "Content review").
|
| group: "research" (research and writing skills) or "discipline"
| (subject-specific support).
|
*/

$standardLimits = 'We do not write work for you to submit as your own, sit tests or exams, or log in to university systems on your behalf.';

return [

    'assignment-planning' => [
        'title' => 'Assignment planning and academic guidance',
        'short' => 'Break down a brief, interpret the marking criteria and plan a clear structure before you start writing.',
        'group' => 'research',
        'icon' => 'clipboard-list',
        'scope' => 'We help you read an assignment brief closely, identify what the task and marking rubric are really asking for, and turn that into a realistic plan: section headings, word-count allocation, key sources to look for and a timeline that fits your deadline.',
        'audience' => 'Undergraduate and postgraduate students who are unsure how to approach a new type of assessment, or who are returning to study after a break.',
        'process' => ['You share the brief, rubric and any lecture guidance.', 'We review the requirements and agree what you need help with.', 'We talk through the brief with you and agree a structure.', 'You receive a written plan you can work from.'],
        'deliverables' => ['Annotated reading of the brief and marking criteria', 'Suggested structure with word-count allocation', 'Checklist of requirements to confirm before submission'],
        'limits' => $standardLimits,
    ],

    'research-proposals' => [
        'title' => 'Research proposal support',
        'short' => 'Shape a researchable question, a justified methodology and a realistic plan for your proposal.',
        'group' => 'research',
        'icon' => 'lightbulb',
        'scope' => 'Guidance on narrowing a topic into a focused research question, positioning it within existing literature, choosing a method you can defend, and addressing ethics, feasibility and timelines.',
        'audience' => 'Final-year undergraduates, master\'s students and anyone preparing a proposal for a dissertation, project or research degree application.',
        'process' => ['You share your topic ideas and your institution\'s proposal template.', 'We discuss possible directions and their feasibility.', 'We give structured feedback on your draft proposal.', 'A follow-up review is available under the agreed terms.'],
        'deliverables' => ['Feedback on research questions and aims', 'Suggestions on methodology and ethics sections', 'Comments on your draft against the template'],
        'limits' => $standardLimits,
    ],

    'literature-reviews' => [
        'title' => 'Literature review support',
        'short' => 'Learn to search, select and synthesise sources into an argument rather than a list of summaries.',
        'group' => 'research',
        'icon' => 'library',
        'scope' => 'We explain search strategies for academic databases, how to judge the quality and relevance of sources, and how to organise a review thematically so it builds an argument and identifies gaps.',
        'audience' => 'Students writing a standalone literature review, or the review chapter of a dissertation or thesis.',
        'process' => ['You share your topic, research question and current reading.', 'We suggest search terms, databases and a review structure.', 'We give feedback on your synthesis and critical analysis.'],
        'deliverables' => ['Search strategy and keyword suggestions', 'Thematic structure for the review', 'Feedback on synthesis, criticality and gaps'],
        'limits' => $standardLimits,
    ],

    'dissertation-guidance' => [
        'title' => 'Dissertation and thesis guidance',
        'short' => 'Chapter-by-chapter guidance and feedback across a long research project, at a pace that suits you.',
        'group' => 'research',
        'icon' => 'book-open',
        'scope' => 'Ongoing support across the stages of a dissertation or thesis: refining the proposal, planning chapters, methodology, analysis and discussion, and preparing for supervisor meetings. Your supervisor remains your primary academic adviser.',
        'audience' => 'Undergraduate and master\'s students completing a dissertation, capstone or major project.',
        'process' => ['We review your proposal, supervisor feedback and timeline.', 'We agree milestones and the type of feedback you need at each one.', 'You share drafts; we return written feedback and discuss it with you.'],
        'deliverables' => ['Agreed milestone plan', 'Written feedback on chapter drafts', 'Guidance on aligning with supervisor comments'],
        'limits' => $standardLimits,
    ],

    'research-methodology' => [
        'title' => 'Research methodology',
        'short' => 'Understand research philosophy, design, sampling and data collection well enough to justify your choices.',
        'group' => 'research',
        'icon' => 'flask-conical',
        'scope' => 'Plain-English explanations of research philosophies, approaches, strategies, sampling, data collection instruments, validity, reliability and ethics, applied to your own project.',
        'audience' => 'Students writing a methodology chapter or a research methods assessment.',
        'process' => ['You share your research question and proposed approach.', 'We explain the options and the trade-offs.', 'We review your methodology draft and suggest improvements.'],
        'deliverables' => ['Explanation of suitable designs for your question', 'Feedback on instruments such as surveys or interview guides', 'Comments on your methodology chapter'],
        'limits' => $standardLimits,
    ],

    'qualitative-quantitative-research' => [
        'title' => 'Qualitative and quantitative research',
        'short' => 'Guidance on interviews, thematic analysis, surveys and statistical approaches, and how to report them.',
        'group' => 'research',
        'icon' => 'scale',
        'scope' => 'Support with designing and analysing qualitative studies (interviews, focus groups, thematic or content analysis) and quantitative studies (questionnaires, descriptive and inferential statistics), including mixed-methods designs.',
        'audience' => 'Students collecting or analysing primary data for a project or dissertation.',
        'process' => ['You describe your data and research question.', 'We recommend an analysis approach and explain it.', 'We review your analysis write-up for accuracy and clarity.'],
        'deliverables' => ['Recommended analysis approach', 'Walk-through of coding or statistical steps', 'Feedback on your findings chapter'],
        'limits' => $standardLimits,
    ],

    'data-analysis' => [
        'title' => 'Data analysis guidance',
        'short' => 'Make sense of datasets, choose suitable tests and visualisations, and interpret results correctly.',
        'group' => 'research',
        'icon' => 'chart-column',
        'scope' => 'Guidance on cleaning and exploring data, selecting statistical tests, building clear charts and dashboards, and interpreting output. We explain the reasoning so you can carry out and defend the analysis yourself.',
        'audience' => 'Business, IT, social-science and engineering students working with data in assignments or projects.',
        'process' => ['You share the task, dataset description and software required.', 'We explain suitable methods and how to apply them.', 'We review your interpretation and presentation of results.'],
        'deliverables' => ['Analysis plan', 'Explanations of tests and visualisation choices', 'Feedback on interpretation and reporting'],
        'limits' => $standardLimits.' We do not claim expertise in specialist or licensed software unless confirmed for your request.',
    ],

    'proofreading-editing' => [
        'title' => 'Proofreading, editing and referencing',
        'short' => 'Clearer language, consistent formatting and correct referencing, while keeping your own argument and voice.',
        'group' => 'research',
        'icon' => 'pen-line',
        'scope' => 'Proofreading for grammar, spelling, punctuation and clarity, with tracked suggestions you accept or reject. Referencing checks for Harvard, APA, MLA, OSCOLA and other styles. We follow your institution\'s proofreading policy and do not change the substance of your work.',
        'audience' => 'Students who want their own finished draft checked before submission, particularly those writing in English as an additional language.',
        'process' => ['You share your draft, the referencing style and your institution\'s proofreading policy if it has one.', 'We confirm scope and timeline.', 'You receive tracked changes and comments.'],
        'deliverables' => ['Tracked-changes document', 'Comments on clarity and structure', 'Reference list and in-text citation checks'],
        'limits' => 'We only proofread work you have written. We do not rewrite content, add arguments or sources, or edit beyond what your institution permits.',
    ],

    'presentations-reports' => [
        'title' => 'Presentations, reports and group-project guidance',
        'short' => 'Plan reports and slide decks, rehearse delivery and organise group work fairly and effectively.',
        'group' => 'research',
        'icon' => 'presentation',
        'scope' => 'Guidance on business and technical report formats, slide design and storytelling, rehearsal and delivery, and organising group projects: dividing tasks, tracking progress and integrating contributions.',
        'audience' => 'Students preparing individual or group reports, posters and presentations.',
        'process' => ['You share the brief and your draft outline or slides.', 'We give feedback on structure, clarity and design.', 'Optional rehearsal feedback by video call.'],
        'deliverables' => ['Report or slide structure feedback', 'Design and readability suggestions', 'Delivery and timing tips'],
        'limits' => $standardLimits,
    ],

    'business-management' => [
        'title' => 'Business and management',
        'short' => 'Strategy, leadership, operations, entrepreneurship and governance, explained through frameworks and cases.',
        'group' => 'discipline',
        'icon' => 'briefcase',
        'scope' => 'Subject guidance across strategic management, leadership, operations and project management, business environment analysis, entrepreneurship, innovation and corporate governance, including how to apply frameworks such as PESTLE, Porter\'s Five Forces, the resource-based view and stakeholder theory to real organisations.',
        'audience' => 'Business, management and MBA students at undergraduate and postgraduate level.',
        'process' => ['You share the module brief and case organisation.', 'We explain relevant theory and how to apply it critically.', 'We give feedback on your analysis and recommendations.'],
        'deliverables' => ['Explanations of relevant frameworks', 'Feedback on case analysis', 'Guidance on evidence and recommendations'],
        'limits' => $standardLimits,
    ],

    'marketing-consumer-behaviour' => [
        'title' => 'Marketing and consumer behaviour',
        'short' => 'Digital marketing, branding, communications and consumer research, from theory to campaign planning.',
        'group' => 'discipline',
        'icon' => 'megaphone',
        'scope' => 'Guidance on marketing management, digital marketing, brand communication and reputation, advertising campaigns, consumer behaviour theory and marketing research methods.',
        'audience' => 'Marketing, business and hospitality students working on plans, campaign reports and research tasks.',
        'process' => ['You share the brief and chosen brand or market.', 'We explain the relevant models and research approaches.', 'We give feedback on your plan or report.'],
        'deliverables' => ['Framework explanations (segmentation, positioning, customer journey and others)', 'Feedback on marketing plans and campaign rationales', 'Research design suggestions'],
        'limits' => $standardLimits,
    ],

    'human-resource-management' => [
        'title' => 'Human resource management',
        'short' => 'HRM, international HRM, talent, people skills and reflective professional development.',
        'group' => 'discipline',
        'icon' => 'users',
        'scope' => 'Guidance on HRM in its business context, international HRM, talent resourcing and development, managing people, management skills and reflective practice, including how to use models such as Gibbs\' reflective cycle well.',
        'audience' => 'HRM, business and management students, including those writing reflective journals or professional development portfolios.',
        'process' => ['You share the brief and any organisational context.', 'We explain relevant theory and evidence.', 'We give feedback on your draft or reflection.'],
        'deliverables' => ['Theory and policy explanations', 'Feedback on reflective writing', 'Guidance on linking theory to practice'],
        'limits' => $standardLimits,
    ],

    'accounting-finance' => [
        'title' => 'Accounting and finance',
        'short' => 'Financial statements, ratios, financial management and financial literacy, explained step by step.',
        'group' => 'discipline',
        'icon' => 'calculator',
        'scope' => 'Guidance on interpreting financial statements, ratio and performance analysis, budgeting, investment appraisal, strategic financial management and financial literacy topics.',
        'audience' => 'Accounting, finance and general business students preparing business reports and analytical assignments.',
        'process' => ['You share the brief and data provided.', 'We explain the techniques and how to interpret results.', 'We give feedback on your calculations and commentary.'],
        'deliverables' => ['Worked explanations of techniques', 'Feedback on analysis and commentary', 'Checks on presentation of financial data'],
        'limits' => $standardLimits,
    ],

    'it-computing' => [
        'title' => 'IT, computing and information systems',
        'short' => 'Databases, programming concepts, systems development, networks, analytics and IS strategy.',
        'group' => 'discipline',
        'icon' => 'cpu',
        'scope' => 'Guidance on data modelling and database design (including entity relationship diagrams), programming fundamentals, system development methodologies, networks and communication, information systems strategy, big data analytics, data visualisation and project and change management in ICT.',
        'audience' => 'Students on information systems, IT, computing and business technology programmes, including weekly practical activities.',
        'process' => ['You share the brief, weekly tasks or case study.', 'We explain the concepts and how to approach the task.', 'We review your work and explain any issues.'],
        'deliverables' => ['Concept explanations with examples', 'Feedback on diagrams, designs and code structure', 'Guidance on technical reports and reflections'],
        'limits' => $standardLimits,
    ],

    'engineering-technical' => [
        'title' => 'Engineering and technical academic support',
        'short' => 'Thermo-fluids, energy systems, modelling, materials and engineering management, with clear explanations.',
        'group' => 'discipline',
        'icon' => 'cog',
        'scope' => 'Guidance on engineering coursework topics such as thermo-fluid systems, power generation, sustainable energy, modelling and simulation concepts, materials processing, research methods and engineering or operations management.',
        'audience' => 'Engineering students, particularly at master\'s level, working on technical reports and case studies.',
        'process' => ['You share the brief and any data or models provided.', 'We confirm whether the topic is within our experience.', 'We explain the theory and review your approach and reporting.'],
        'deliverables' => ['Theory explanations and worked principles', 'Feedback on technical reports', 'Guidance on structuring case studies'],
        'limits' => $standardLimits.' We do not have access to specialist laboratories, and support involving licensed simulation software is confirmed case by case.',
    ],

    'hospitality-tourism-events' => [
        'title' => 'Hospitality, tourism and events management',
        'short' => 'Resort, accommodation and food production management, hospitality strategy and events.',
        'group' => 'discipline',
        'icon' => 'hotel',
        'scope' => 'Guidance on hospitality business strategy, tourist resort management, accommodation and food production management, hospitality marketing and events management, including reports and case studies.',
        'audience' => 'Hospitality, tourism and events students at diploma, undergraduate and postgraduate level.',
        'process' => ['You share the unit brief and assessment criteria.', 'We explain relevant industry frameworks and examples.', 'We give feedback on your report or case study.'],
        'deliverables' => ['Framework and industry explanations', 'Feedback on reports against criteria', 'Guidance on using industry evidence'],
        'limits' => $standardLimits,
    ],

    'law-legal-research' => [
        'title' => 'Law and legal academic research',
        'short' => 'Legal research skills, case analysis, OSCOLA referencing and structured legal writing.',
        'group' => 'discipline',
        'icon' => 'landmark',
        'scope' => 'Academic guidance on legal research methods, reading and analysing cases and legislation, structuring problem questions and essays (for example with IRAC), writing legal memoranda for coursework and OSCOLA referencing.',
        'audience' => 'Law students working on coursework assignments.',
        'process' => ['You share the coursework question and module materials.', 'We explain research and structuring approaches.', 'We give feedback on your draft.'],
        'deliverables' => ['Research strategy suggestions', 'Feedback on structure and use of authority', 'Referencing checks'],
        'limits' => $standardLimits.' This is academic support only. We are not a law firm and do not give legal advice.',
    ],

];
