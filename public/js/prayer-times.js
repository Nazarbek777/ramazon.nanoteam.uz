/**
 * Namoz vaqtlari va Ro'za vaqtlari — IslomAPI.uz (Monthly Caching)
 */

const PrayerTimes = {
    CACHE_KEY: 'ramazon_prayer_month_', // Will append month_year
    LOCATION_KEY: 'ramazon_location_v2',

    // O'zbekiston default koordinatalari (Toshkent)
    DEFAULT_LAT: 41.2995,
    DEFAULT_LON: 69.2401,

    names: {
        Fajr: 'Bomdod',
        Sunrise: 'Quyosh',
        Dhuhr: 'Peshin',
        Asr: 'Asr',
        Maghrib: 'Shom',
        Isha: 'Xufton'
    },

    icons: {
        Fajr: 'ri-sun-line',
        Sunrise: 'ri-sun-foggy-line',
        Dhuhr: 'ri-sun-fill',
        Asr: 'ri-cloud-line',
        Maghrib: 'ri-moon-line',
        Isha: 'ri-moon-clear-fill'
    },

    /** Lokatsiyani olish */
    getLocation() {
        return new Promise((resolve) => {
            const saved = localStorage.getItem(this.LOCATION_KEY);
            if (saved) {
                try {
                    const loc = JSON.parse(saved);
                    // Force fix Tashkent in saved location
                    if (loc.apiRegion && loc.apiRegion.toLowerCase() === 'tashkent') {
                        loc.apiRegion = 'Toshkent';
                        localStorage.setItem(this.LOCATION_KEY, JSON.stringify(loc));
                    }
                    if (loc.lat && loc.lon && loc.displayCity) {
                        resolve(loc);
                        return;
                    }
                } catch (e) { /* ignore */ }
            }

            if (!navigator.geolocation) {
                const def = { lat: this.DEFAULT_LAT, lon: this.DEFAULT_LON, displayCity: 'Toshkent shahri', apiRegion: 'Toshkent' };
                localStorage.setItem(this.LOCATION_KEY, JSON.stringify(def));
                resolve(def);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const loc = {
                        lat: pos.coords.latitude,
                        lon: pos.coords.longitude,
                        displayCity: null,
                        apiRegion: null
                    };

                    this.getCityName(loc.lat, loc.lon).then(res => {
                        loc.displayCity = res.displayCity;
                        loc.apiRegion = res.apiRegion;
                        localStorage.setItem(this.LOCATION_KEY, JSON.stringify(loc));
                        resolve(loc);
                    });
                },
                () => {
                    const def = { lat: this.DEFAULT_LAT, lon: this.DEFAULT_LON, displayCity: 'Toshkent shahri', apiRegion: 'Toshkent' };
                    localStorage.setItem(this.LOCATION_KEY, JSON.stringify(def));
                    resolve(def);
                },
                { timeout: 3000, maximumAge: 86400000 }
            );
        });
    },

    /** Shahar nomini olish (Reverse Geocoding) + API'ga mos regionni tozalash */
    async getCityName(lat, lon) {
        // IslomAPI tomonidan qo'llab-quvvatlanadigan asosiy hududlar va viloyat markazlari
        const regionMapping = {
            // Toshkent
            'toshkent': 'Toshkent', 'tashkent': 'Toshkent', 'chirchiq': 'Toshkent', 'angren': 'Toshkent', 'olmaliq': 'Toshkent', 'bekobod': 'Toshkent',
            // Farg'ona vodiysi
            'farg\'ona': 'Farg\'ona', 'fergana': 'Farg\'ona', 'marg\'ilon': 'Farg\'ona', 'qo\'qon': 'Qo\'qon', 'kokand': 'Qo\'qon', 'quva': 'Farg\'ona',
            'andijon': 'Andijon', 'andizhan': 'Andijon', 'asaka': 'Andijon', 'xo\'jaobod': 'Andijon', 'shahrixon': 'Andijon',
            'namangan': 'Namangan', 'chust': 'Namangan', 'uchqo\'rg\'on': 'Namangan', 'kosonsoy': 'Namangan',
            // Markaziy viloyatlar
            'samarqand': 'Samarqand', 'samarkand': 'Samarqand', 'kattaqo\'rg\'on': 'Samarqand', 'urgut': 'Samarqand',
            'buxoro': 'Buxoro', 'bukhara': 'Buxoro', 'gijduvon': 'Buxoro', 'kogon': 'Buxoro',
            'navoiy': 'Navoiy', 'navoi': 'Navoiy', 'zarafshon': 'Zarafshon', 'uchquduq': 'Navoiy',
            'jizzax': 'Jizzax', 'jizakh': 'Jizzax', 'paxtakor': 'Jizzax',
            'guliston': 'Guliston', 'sirdaryo': 'Guliston', 'yangiyer': 'Guliston', 'shirin': 'Guliston',
            // Janubiy viloyatlar
            'qarshi': 'Qarshi', 'karshi': 'Qarshi', 'qashqadaryo': 'Qarshi', 'shaxrisabz': 'Qarshi', 'muborak': 'Qarshi',
            'termiz': 'Termiz', 'termez': 'Termiz', 'surxondaryo': 'Termiz', 'denov': 'Termiz', 'sherobod': 'Termiz',
            // G'arbiy viloyatlar
            'urganch': 'Urganch', 'urgench': 'Urganch', 'xorazm': 'Urganch', 'xiva': 'Xiva', 'khiva': 'Xiva',
            'nukus': 'Nukus', 'qoraqalpog\'iston': 'Nukus', 'karakalpakstan': 'Nukus', 'mo\'ynoq': 'Nukus', 'qo\'ng\'irot': 'Nukus'
        };

        try {
            const geoResp = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&accept-language=uz,en&zoom=10`);
            const geoData = await geoResp.json();
            const addr = geoData.address;
            if (!addr) return { displayCity: 'Toshkent shahri', apiRegion: 'Toshkent' };

            const city = addr.city || addr.town || addr.village || addr.suburb || addr.hamlet || '';
            const province = addr.state || addr.region || addr.county || '';
            const locationString = `${city} ${province} ${addr.display_name || ''}`.toLowerCase();

            // API uchun regionni aniqlash
            let apiRegion = 'Toshkent';
            for (const [key, val] of Object.entries(regionMapping)) {
                if (locationString.includes(key)) {
                    apiRegion = val;
                    break;
                }
            }

            // Tashrif buyurilgan shahar va tumanni chiroyli chiqarish
            const district = addr.district || addr.city_district || addr.suburb || '';
            let displayCity = '';

            if (district && city && district !== city) {
                displayCity = `${district}, ${city}`;
            } else {
                displayCity = city || province || 'Toshkent shahri';
            }

            // Agar juda qisqa bo'lib qolsa yoki faqat davlat bo'lsa
            if (!displayCity || displayCity === 'Oʻzbekiston') displayCity = 'Toshkent shahri';

            return { displayCity, apiRegion };
        } catch (e) {
            console.error('Location Error:', e);
            return { displayCity: 'Toshkent shahri', apiRegion: 'Toshkent' };
        }
    },

    async fetchMonthData(region, month, year) {
        let sanitizedRegion = (region || 'Toshkent').toString().trim();

        // IslomAPI kutadigan aniq nomlar
        const normalizeMap = {
            'tashkent': 'Toshkent', 'toshkent': 'Toshkent',
            'andijan': 'Andijon', 'andizhan': 'Andijon', 'andijon': 'Andijon',
            'bukhara': 'Buxoro', 'buxoro': 'Buxoro',
            'fergana': 'Farg\'ona', 'fergane': 'Farg\'ona', 'farg\'ona': 'Farg\'ona',
            'namangan': 'Namangan',
            'gulistan': 'Guliston', 'guliston': 'Guliston',
            'jizakh': 'Jizzax', 'jizzax': 'Jizzax',
            'samarkand': 'Samarqand', 'samarqand': 'Samarqand',
            'karshi': 'Qarshi', 'qarshi': 'Qarshi',
            'termez': 'Termiz', 'termiz': 'Termiz',
            'navoi': 'Navoiy', 'navoiy': 'Navoiy',
            'urgench': 'Urganch', 'urganch': 'Urganch',
            'nukus': 'Nukus',
            'khiva': 'Xiva', 'xiva': 'Xiva',
            'kokand': 'Qo\'qon', 'qo\'qon': 'Qo\'qon',
            'zarafshon': 'Zarafshon'
        };

        const lowerReg = sanitizedRegion.toLowerCase();
        if (normalizeMap[lowerReg]) {
            sanitizedRegion = normalizeMap[lowerReg];
        }

        const cacheKey = `${this.CACHE_KEY}${sanitizedRegion}_${month}_${year}`;
        const cached = localStorage.getItem(cacheKey);

        if (cached) {
            try {
                const data = JSON.parse(cached);
                if (data && data.length > 0) return data;
            } catch (e) { localStorage.removeItem(cacheKey); }
        }

        try {
            const url = `https://islomapi.uz/api/monthly?region=${encodeURIComponent(sanitizedRegion)}&month=${month}`;
            const resp = await fetch(url);
            const data = await resp.json();
            if (data && Array.isArray(data) && data.length > 0) {
                localStorage.setItem(cacheKey, JSON.stringify(data));
                return data;
            } else {
                console.warn('API bo\'sh natija qaytardi:', sanitizedRegion);
            }
        } catch (e) {
            console.error('IslomAPI Error:', e);
        }
        return null;
    },

    currentDay: new Date(),

    /** Bugungi namoz vaqtlarini olish (muayyan sana uchun) */
    async getTimesForDate(region, date) {
        const d = date.getDate();
        const m = date.getMonth() + 1;
        const y = date.getFullYear();

        const monthData = await this.fetchMonthData(region, m, y);
        if (monthData && monthData.length > 0) {
            const dayData = monthData.find(item => item.day === d);
            const target = dayData || monthData[d - 1];

            if (target && target.times) {
                return {
                    Fajr: target.times.tong_saharlik,
                    Sunrise: target.times.quyosh,
                    Dhuhr: target.times.peshin,
                    Asr: target.times.asr,
                    Maghrib: target.times.shom_iftor,
                    Isha: target.times.hufton,
                    dateText: target.date.split(',')[0] + ' ' + target.weekday
                };
            }
        }
        return null;
    },

    /** Custom sana uchun render qilish */
    async changeDate(offset) {
        this.currentDay.setDate(this.currentDay.getDate() + offset);
        await this.render();
    },

    /** Keyingi namozni aniqlash */
    getNextPrayer(times) {
        if (!times) return null;
        const now = new Date();
        const currentMinutes = now.getHours() * 60 + now.getMinutes();
        const prayerOrder = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];

        for (const name of prayerOrder) {
            const t = times[name];
            if (!t) continue;
            const [h, m] = t.split(':').map(Number);
            const prayerMinutes = h * 60 + m;
            if (prayerMinutes > currentMinutes) {
                return {
                    name: name,
                    uzName: this.names[name],
                    time: t,
                    icon: this.icons[name],
                    minutesLeft: prayerMinutes - currentMinutes
                };
            }
        }
        return {
            name: 'Fajr',
            uzName: this.names['Fajr'],
            time: times['Fajr'],
            icon: this.icons['Fajr'],
            minutesLeft: null
        };
    },

    formatRemaining(minutes) {
        if (!minutes) return '';
        const h = Math.floor(minutes / 60);
        const m = minutes % 60;
        return h > 0 ? `${h}s ${m}d` : `${m}d`;
    },

    /** Navbar lokatsiyasini yangilash */
    updateNavbarLocation(city) {
        const fullDisplay = city.includes('shahri') ? city : `${city}`;
        document.querySelectorAll('#navLocationMobile .loc-text, #navLocationDesktop .loc-text').forEach(el => {
            el.textContent = fullDisplay;
        });
    },

    /** Joylashuvni yangilash (Cache'ni tozalash) */
    async refreshLocation() {
        const btns = document.querySelectorAll('.refresh-loc-btn');
        btns.forEach(btn => btn.style.animation = 'spin 1s linear infinite');

        // Faqat lokatsiya va shu oyning namoz vaqtlarini tozalash
        localStorage.removeItem(this.LOCATION_KEY);
        // Barcha oylik namoz keshlarini tozalash (ixtiyoriy)
        const keysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith(this.CACHE_KEY)) keysToRemove.push(key);
        }
        keysToRemove.forEach(k => localStorage.removeItem(k));

        await this.render();
        btns.forEach(btn => btn.style.animation = 'none');
    },

    /** UI'ni yangilash */
    async render() {
        const container = document.getElementById('prayerTimesWidget');
        const loc = await this.getLocation();

        // Navbar sync
        this.updateNavbarLocation(loc.displayCity);

        if (!container) return;

        const isToday = this.currentDay.toDateString() === new Date().toDateString();
        const times = await this.getTimesForDate(loc.apiRegion, this.currentDay);

        if (!times) {
            container.innerHTML = `<div class="text-center p-3"><i class="ri-wifi-off-line"></i> Ma'lumot topilmadi</div>`;
            return;
        }

        const nextPrayer = isToday ? this.getNextPrayer(times) : null;

        // Dashboard suhoor/iftar slots (faqat bugun uchun)
        if (isToday) {
            const suhoorEl = document.getElementById('suhoorTime');
            const iftarEl = document.getElementById('iftarTime');
            if (suhoorEl) suhoorEl.textContent = times.Fajr;
            if (iftarEl) iftarEl.textContent = times.Maghrib;
        }

        const prayerList = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
        let prayerRows = '';

        prayerList.forEach(name => {
            const t = times[name];
            const isNext = nextPrayer && nextPrayer.name === name;
            prayerRows += `
                <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-bottom:1px solid var(--white-5);${isNext ? 'background:var(--accent-bg);border-radius:10px;border:1px solid var(--border-color);' : ''}">
                    <i class="${this.icons[name]}" style="font-size:1.1rem;width:24px;color:${isNext ? 'var(--accent)' : 'var(--text-muted)'};"></i>
                    <span style="flex:1;font-size:0.85rem;font-weight:${isNext ? '700' : '500'};color:${isNext ? 'var(--text-primary)' : 'var(--text-secondary)'};">${this.names[name]}</span>
                    <span style="font-size:0.9rem;font-weight:700;color:${isNext ? 'var(--accent)' : 'var(--text-primary)'};">${t}</span>
                    ${isNext && nextPrayer.minutesLeft ? `<span style="font-size:0.65rem;color:var(--accent);background:var(--accent-bg);padding:2px 6px;border-radius:4px;margin-left:8px;">-${this.formatRemaining(nextPrayer.minutesLeft)}</span>` : ''}
                </div>
            `;
        });

        const dateString = this.currentDay.toLocaleDateString('uz-UZ', { day: 'numeric', month: 'long', weekday: 'long' });

        container.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;padding:0 4px;">
                <button onclick="PrayerTimes.changeDate(-1)" style="background:none;border:none;color:var(--gold);cursor:pointer;padding:5px;"><i class="ri-arrow-left-s-line"></i></button>
                <div style="text-align:center;">
                    <div style="font-size:0.85rem;font-weight:700;color:var(--text-primary);">${dateString}</div>
                    <div style="font-size:0.7rem;color:var(--text-secondary);"><i class="ri-map-pin-2-line"></i> ${loc.displayCity}</div>
                </div>
                <button onclick="PrayerTimes.changeDate(1)" style="background:none;border:none;color:var(--gold);cursor:pointer;padding:5px;"><i class="ri-arrow-right-s-line"></i></button>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">${prayerRows}</div>
            ${!isToday ? `<button onclick="this.disabled=true;PrayerTimes.currentDay=new Date();PrayerTimes.render()" style="width:100%;margin-top:12px;background:var(--gold-bg);border:1px solid var(--gold);color:var(--gold);border-radius:8px;padding:8px;font-size:0.75rem;cursor:pointer;">Bugunga qaytish</button>` : ''}
        `;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    PrayerTimes.render();
    setInterval(() => PrayerTimes.render(), 60000); // Har daqiqada tekshirish
});
