# SunnyTool - เครื่องมือออนไลน์

เว็บไซต์รวบรวมเครื่องมือออนไลน์ที่มีประโยชน์สำหรับการทำงานแบบ All-in-One

## 🌟 คุณสมบัติหลัก

- **หน้าหลัก Dashboard** - แสดงเครื่องมือทั้งหมดในรูปแบบ Card Portal
- **Sidebar Navigation ทุกหน้า** - เมนูด้านข้างสำหรับการนำทางที่สะดวกในทุกหน้า
- **Responsive Design** - รองรับการใช้งานบนมือถือและเดสก์ท็อป
- **การนำทางแบบไหลลื่น** - สลับระหว่างเครื่องมือได้อย่างง่ายดายผ่าน sidebar
- **Material Design** - UI ที่สะอาดตาและทันสมัยตาม Google Material Design

## 📁 โครงสร้างโปรเจค

```
SunnyTool/
│
├── index.html                          # หน้าหลัก Dashboard
├── assets/                             # โฟลเดอร์สำหรับไฟล์ทรัพยากรร่วม
│   └── shared.css                     # CSS ร่วมสำหรับ Header, Sidebar และ Material Design
└── tools/                              # โฟลเดอร์รวมเครื่องมือทั้งหมด
    ├── thai_text_fixer.html           # เครื่องมือแก้ไขข้อความไทยจาก PDF
    ├── clean_googlesheet_data.html    # เครื่องมือลบข้อมูลซ้ำ
    └── clean_googlesheet_data.html    # เครื่องมืออ่าน Log File
```

## 🛠️ เครื่องมือที่มีอยู่

### 1. เครื่องมือแก้ไขข้อความไทยจาก PDF
- **ไฟล์**: `tools/thai_text_fixer.html`
- **ฟังก์ชัน**: แก้ไขปัญหาข้อความไทยที่คัดลอกมาจาก PDF ที่มีสระและวรรณยุกต์แยกออกจากตัวอักษร
- **คุณสมบัติ**: 
  - แก้สระ ำ ที่หายไป
  - รวมสระและวรรณยุกต์ที่แยกออก
  - แสดงรายการการแก้ไขที่ทำไป
  - สถิติการแก้ไข

### 2. เครื่องมือลบข้อมูลซ้ำจาก Google Sheets
- **ไฟล์**: `tools/clean_googlesheet_data.html`
- **ฟังก์ชัน**: ลบข้อมูลที่ซ้ำกันในรายการ
- **คุณสมบัติ**:
  - ลบบรรทัดที่ซ้ำกัน
  - แสดงรายการข้อมูลที่ถูกลบ
  - สถิติข้อมูลก่อนและหลัง
  - คัดลอกผลลัพธ์เป็น Array

### 3. เครื่องมืออ่าน Log File
- **ไฟล์**: `tools/log_reader.html`
- **ฟังก์ชัน**: วิเคราะห์และแสดงผล Log File ของฐานข้อมูลให้อ่านง่ายขึ้น
- **คุณสมบัติ**:
  - **Fullscreen Mode**: ขยายหน้าจอ log viewer เพื่อการดูที่ละเอียดยิ่งขึ้น พร้อม layout แบบสองคอลัมน์
  - **Syntax Highlighting**: เน้นสีส่วนประกอบต่างๆ ของ SQL command เช่น `timestamp`, `ip address`, `user`, `command`, และ `table name`
  - **ค้นหาและกรอง**: ค้นหาข้อความใน log และกรองตามประเภทของคำสั่ง SQL (`INSERT`, `UPDATE`, `DELETE`, `SELECT`)
  - **สถิติ**: แสดงข้อมูลสรุปจำนวน log ทั้งหมด, log ที่แสดง, และจำนวนแต่ละคำสั่ง
  - **ปุ่มควบคุม**: 
    - `แสดงทั้งหมด`: แสดง log ทั้งหมดโดยไม่ใช้ filter
    - `Clear Filter`: ล้างเงื่อนไขการกรองและการค้นหาทั้งหมด

## 🚀 วิธีใช้งาน

1. เปิดไฟล์ `index.html` ในเว็บเบราว์เซอร์
2. เลือกเครื่องมือที่ต้องการใช้จาก Card Portal หรือ Sidebar
3. ใช้งานเครื่องมือตามที่ต้องการ
4. ใช้ Sidebar เพื่อสลับไปใช้เครื่องมืออื่น หรือกลับหน้าหลัก

## 🎨 การออกแบบ

- **ธีม**: Material Design
- **สีหลัก**: Material Blue (#2196F3), Material Green (#4CAF50)
- **Font**: Roboto สำหรับภาษาอังกฤษ, Noto Sans Thai สำหรับภาษาไทย
- **Icons**: Material Icons
- **Layout**: Cards, Shadows และ Typography ตาม Material Design Guidelines
- **Responsive**: รองรับหน้าจอขนาดต่างๆ พร้อม Mobile-First Approach

## 🔧 การพัฒนา

### การเพิ่มเครื่องมือใหม่

1. สร้างไฟล์ HTML ใหม่ในโฟลเดอร์ `tools/`
2. เพิ่มลิงค์ `<link href="../assets/shared.css" rel="stylesheet">`
3. คัดลอก HTML structure (Header + Sidebar + Main Content) จากไฟล์เดิม
4. ปรับ class `active` ใน sidebar navigation ให้ตรงกับหน้าปัจจุบัน
5. เพิ่ม Card ใหม่ใน `index.html`
6. เพิ่มลิงค์ใน Sidebar Navigation ของทุกไฟล์

### การจัดการ CSS

- **Shared CSS**: ไฟล์ `assets/shared.css` ประกอบด้วย:
  - Navigation Header และ Sidebar styles
  - Material Design base styles
  - Responsive design rules
  - Common component styles
- **Page-specific CSS**: CSS เฉพาะหน้าอยู่ใน `<style>` ของแต่ละไฟล์
- **การเพิ่ม Styles ใหม่**: แก้ไขใน `assets/shared.css` สำหรับ global styles หรือใน `<style>` สำหรับ page-specific styles

## 📱 การใช้งานบนมือถือ

- Sidebar จะซ่อนอัตโนมัติและใช้ hamburger menu
- Cards จะจัดเรียงเป็นคอลัมน์เดียว
- ปุ่มและฟอร์มปรับขนาดให้เหมาะกับการสัมผัส

## 🌐 Browser Support

- Chrome (แนะนำ)
- Firefox
- Safari
- Edge

---

**พัฒนาโดย**: SunnyTool Team  
**อัปเดตล่าสุด**: September 2025
