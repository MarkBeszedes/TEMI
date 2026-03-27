
# TEMI 
Tumor Elemzés Mesterséges Intelligenciával (stands for: Tumor Analysis with Artificial Intelligence)

This is one of my secondary school projects; for me, it was kind of like an introduction to medical AI, php gateways, data pipelines, and web dev as well. I did use the help and knowledge of some of the best programmers & mentors in the world: claude sonnet and chat.gpt
Mostly I did the coding and the research, eating up around 650-700 h of coding and approximately 150-200 h of research.

If you whould like to visit the patient's side of the project, you can do it here: [TEMI](temi.infinityfreeapp.com)
 
---
 
## 🩺 The Problem:
 
Cancer is the second leading cause of death worldwide. In 2022, **9.7 million people died from cancer globally** — roughly 1 in 6 deaths. In Hungary alone, approximately **30,000 patients die annually** from tumor-related diseases.
 
What makes this especially sobering: a significant portion of these deaths are attributable to **human error**, both on the patient side (late detection due to skipped screenings) and the physician side (misdiagnosis). Early-stage detection is critical — colorectal cancer survival rates drop from ~100% at Stage 0 to just ~15–20% at Stage IV.
 
**We don't have any unified international oncological database in the world.**  But TEMI aims to change that.
 
---
 
## 🎯 Project Goal
 
For the solution we need to ask: *How can a system be built that creates an international central database targeting tumor diseases and — using artificial intelligence — minimizes the human error factor for both the physician and the patient?*
 
TEMI is not meant to replace oncologists. It is designed to **augment them** — much like autopilots assist (but don't replace) commercial pilots.
 
---
 
## Solution Hypotheses
 
If AI can distinguish an orange from a mandarin, it can distinguish differences between aspiration cytodiagnostic samples — and detect barely visible tumor changes in X-ray and ultrasound images.
 
TEMI approaches this from **two angles**:
 
**1. Physician-side diagnostics** — A neural network trained on a structured oncological database assists doctors by providing a diagnosis suggestion with around 90%+ accuracy, validated asynchronously by the physician before being saved to the database.
For this I used the Keras framework of TensorFlow, with a binary classification-ended neural network. A logistic regression model would have been way more appropriate to this problem, but the project structure is planned to get even more complex than just a binary classification. 
 
**2. Patient-side self-assessment** — A questionnaire-driven neural network estimates a user's oncological risk profile based on lifestyle factors (smoking, family history, alcohol use, etc.), and recommends how frequently they should visit an oncologist. The idea behind this is if we manage to scare the patient, they will be more conscious. If someone knows they have an 85% chance of developing cancer by age 50, they are far more likely to get screened early. 
 
---
 
## Technical Architecture
 
### Neural Network (Structured Data)
- Built on a **realistic example database from kaggle** (~500+ rows)
- Framework: **TensorFlow**
- Architecture: 3 layers — `256 → 256 → 1` with sigmoid activation function
- Output: binary classification (malignant / benign)
- Training: 10 epochs × 10 cases = 100 validation samples
- Average accuracy: **90%+**
 
### CNN (Image Analysis)
- Based on **MobileNetV2** via **transfer learning**
- Custom trained model: `tumor_image_model.h5`
- Currently focused on **aspiration cytodiagnostic samples**
- Binary output (1 = malignant, 0 = benign) (Not realistic or accurate in this project)
 
### Data Pipeline
- Asynchronous analysis: the AI's output is reviewable and correctable by the physician before committing to the database
- Only the verified, correctly diagnosed cases are stored
- Patient records queryable via personal ID (as a foreign key)
- Plannaed database structured per organ/tissue group, following **Normal Forms** and **ICD-O** standards (1000+ morphological types)
 
### Web Interface
- Home page with patient intake forms
- Organ-area-specific data entry
- Backend listing for specialist review and asynchronous case analysis
- Image-based intake (cytodiagnostic samples)
- Lifestyle-based risk questionnaire with neural network output
- Results queryable by patient ID
 
---
 
## ✨ Values TEMI Represents
 
If fully realized, TEMI would deliver:
 
1. Minimized human error — on both the physician and patient side
2. The world's largest continuously growing oncological case database
3. Simplified access to diagnoses and medical findings
4. Significantly more accurate diagnostics
5. Millions of lives globally
 
The long-term vision is to operate as a **nonprofit**, with services freely accessible to anyone — regardless of location or wealth.
 
---
 
## 🚧 Why Is This Hard?
 
- Tumors are extraordinarily diverse: hundreds of types, each with unique input/output attributes, binarry classification is not enough
- No single model applies across all cancers
- Building a truly universal database requires constant collaboration with specialists across oncological subdisciplines
- Patent research revealed past attempts at similar systems — none succeeded due to oncology's complexity
 
---



## 📚 Key References
 
- [WHO — Global Cancer Burden 2024](https://www.who.int/news/item/01-02-2024-global-cancer-burden-growing--amidst-mounting-need-for-services)
- [ICD-O Standard (WHO)](https://iris.who.int/bitstream/handle/10665/96612/9789241548496_eng.pdf)
- [Training dataset — Breast Cancer Wisconsin (Kaggle)](https://www.kaggle.com/datasets/uciml/breast-cancer-wisconsin-data)
- *Daganatok Aspirációs Citodiagnosztikája* — Dr. Bodó Miklós, Dr. Döbrössy Lajos (1985)
 
---
