/**
* Namoz vaqtlari va Ro'za vaqtlari — Geolocation + Aladhan API
*/

const PrayerTimes = {
    CACHE_KEY: 'ramazon_prayer_data',
    LOCATION_KEY: 'ramazon_location',

    // O'zbekiston default koordinatalari (Toshkent)
    DEFAULT_LAT: 41.2995,
    DEFAULT_LON: 69.2401,

    // Duolar
    duas: {
        suhoor: {
            arabic: 'نَوَيتُ أَن أَصُومَ غَداً مِن شَهرِ رَمَضَانَ المُبَارَكِ فَرضاً لَكَ يَا اللّهُ فَتَقَبَّلْ مِنِّي إِنَّكَ أَنتَ السَّمِيعُ العَلِيمُ',
            uzbek: 'Ertangi Ramazon kunining ro\'zasini tutmoqni niyat qildim. Ya Alloh, qabul ayla, albatta Sen Eshituvchi va Biluvchisan.'
        },
        iftar: {
            arabic: 'اللّهُمَّ لَكَ صُمتُ وَبِكَ آمَنتُ وَعَلَيكَ تَوَكَّلتُ وَعَلَى رِزقِكَ أَفطَرتُ فَاغفِر لِي مَا قَدَّمتُ وَمَا أَخَّرتُ',
            uzbek: 'Yo Alloh, Sen uchun ro\'za tutdim, Senga iymon keltirdim, Senga tavakkul qildim va bergan rizqing bilan og\'iz ochdim. Oldingi va keyingi gunohlarimni mag\'firat ayla.'
        }
    },

    // Namoz nomlari
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
            // Avval cache'dan
            const saved = localStorage.getItem(this.LOCATION_KEY);
            if (saved) {
                try {
                    const loc = JSON.parse(saved);
                    if (loc.lat && loc.lon) {
                        resolve(loc);
                        return;
                    }
                } catch (e) { /* ignore */ }
            }

            if (!navigator.geolocation) {
                resolve({ lat: this.DEFAULT_LAT, lon: this.DEFAULT_LON, city: 'Toshkent' });
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const loc = {
                        lat: pos.coords.latitude,
                        lon: pos.coords.longitude,
                        city: null
                    };
                    localStorage.setItem(this.LOCATION_KEY, JSON.stringify(loc));

                    // Shahar nomini olish
                    this.getCityName(loc.lat, loc.lon).then(city => {
                        loc.city = city;
                        localStorage.setItem(this.LOCATION_KEY, JSON.stringify(loc));
                        resolve(loc);
                    });
                },
                () => {
                    resolve({ lat: this.DEFAULT_LAT, lon: this.DEFAULT_LON, city: 'Toshkent' });
                },
                { timeout: 5000, maximumAge: 3600000 }
            );
        });
    },

    /** Shahar nomini olish */
    async getCityName(lat, lon) {
        try {
            const resp = await fetch(`https://api.aladhan.com/v1/qibla/${lat}/${lon}`);
            // Aladhan doesn't return city, so use reverse geocoding from nominatim
            const geoResp = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&zoom=10`);
            const geoData = await geoResp.json();
            return geoData.address?.city || geoData.address?.town || geoData.address?.state || 'Noma\'lum';
        } catch (e) {
            return null;
        }
    },

    /** Namoz vaqtlari va Ro'za vaqtlari — IslomAPI.uz */
    /** Namoz vaqtlari va Ro'za vaqtlari — IslomAPI.uz */
    async fetchTimes(city) {
        const today = new Date();
        const d = today.getDate();
        const m = today.getMonth() + 1;
        const dateStr = `${d}-${m}-${today.getFullYear()}`;
        const region = city || 'Toshkent';

        // Cache tekshirish
        const cacheKey = `${this.CACHE_KEY}_${region}_${dateStr}`;
        const cached = localStorage.getItem(cacheKey);
        if (cached) {
            try {
                return JSON.parse(cached);
            } catch (e) { /* ignore */ }
        }

        try {
            // /api/present/day 404 berayotgani uchun /api/daily ishlatamiz
            const resp = await fetch(`https://islomapi.uz/api/daily?region=${region}&month=${m}&day=${d}`);
            const data = await resp.json();

            if (data && data.times) {
                // IslomAPI formatini bizning formatga o'tkazish
                const times = {
                    Fajr: data.times.tong_saharlik,
                    Sunrise: data.times.quyosh,
                    Dhuhr: data.times.peshin,
                    Asr: data.times.asr,
                    Maghrib: data.times.shom_iftor,
                    Isha: data.times.hufton
                };
                localStorage.setItem(cacheKey, JSON.stringify(times));
                return times;
            }
        } catch (e) {
            console.error('IslomAPI xatosi:', e);
            // Agar shahar topilmasa Toshkentga harakat
            if (region !== 'Toshkent') return this.fetchTimes('Toshkent');
        }

        return null;
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

        // Agar barcha namozlar o'tgan bo'lsa, keyingi kun Bomdod
        return {
            name: 'Fajr',
            uzName: this.names['Fajr'],
            time: times['Fajr'],
            icon: this.icons['Fajr'],
            minutesLeft: null
        };
    },

    /** Qolgan vaqtni formatlash */
    formatRemaining(minutes) {
        if (!minutes) return '';
        const h = Math.floor(minutes / 60);
        const m = minutes % 60;
        if (h > 0) return `${h} soat ${m} daqiqa`;
        return `${m} daqiqa`;
    },

    /** UI'ni yangilash */
    async render() {
        const container = document.getElementById('prayerTimesWidget');
        if (!container) return;

        container.innerHTML = `
            <div style="text-align:center;padding:16px;">
                <i class="ri-loader-4-line" style="font-size:1.4rem;color:var(--gold);animation:spin 1s linear infinite;"></i>
                <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;">Lokatsiya aniqlanmoqda...</div>
            </div>
        `;

        const loc = await this.getLocation();
        const times = await this.fetchTimes(loc.city);

        if (!times) {
            container.innerHTML = `
                <div style="text-align:center;padding:16px;">
                    <i class="ri-wifi-off-line" style="font-size:1.4rem;color:var(--danger);"></i>
                    <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;">Internet aloqasi yo'q</div>
                </div>
            `;
            return;
        }

        const nextPrayer = this.getNextPrayer(times);
        const cityName = loc.city || 'Joylashuv aniqlandi';
        const suhoorTime = times.Fajr;
        const iftarTime = times.Maghrib;

        // Saharlik / Iftorlik widgetlarini yangilash
        const suhoorEl = document.getElementById('suhoorTime');
        const iftarEl = document.getElementById('iftarTime');
        if (suhoorEl) suhoorEl.textContent = suhoorTime;
        if (iftarEl) iftarEl.textContent = iftarTime;

        // Namoz vaqtlari ro'yxati
        const prayerList = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
        let prayerRows = '';

        prayerList.forEach(name => {
            const t = times[name];
            if (!t) return;
            const isNext = nextPrayer && nextPrayer.name === name;
            prayerRows += `
                <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-bottom:1px solid var(--white-5);${isNext ? 'background:var(--accent-bg);border-radius:8px;border:1px solid var(--border-color);' : ''}">
                    <i class="${this.icons[name]}" style="font-size:1rem;width:20px;color:${isNext ? 'var(--gold)' : 'var(--text-muted)'};"></i>
                    <span style="flex:1;font-size:0.85rem;font-weight:${isNext ? '600' : '400'};color:${isNext ? 'var(--text-primary)' : 'var(--text-secondary)'};">${this.names[name]}</span>
                    <span style="font-size:0.88rem;font-weight:600;color:${isNext ? 'var(--gold)' : 'var(--text-primary)'};">${t}</span>
                    ${isNext && nextPrayer.minutesLeft ? `<span style="font-size:0.65rem;color:var(--accent-light);background:var(--accent-bg);padding:2px 6px;border-radius:4px;">${this.formatRemaining(nextPrayer.minutesLeft)}</span>` : ''}
                </div>
            `;
        });

        container.innerHTML = `
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;">
                <i class="ri-map-pin-2-fill" style="color:var(--accent-light);font-size:0.9rem;"></i>
                <span style="font-size:0.78rem;color:var(--text-muted);">${cityName}</span>
            </div>
            <div>${prayerRows}</div>
        `;
    }
};

// Sahifa yuklanganda ishga tushirish
document.addEventListener('DOMContentLoaded', () => {
    PrayerTimes.render();

    // Har 5 daqiqada yangilash
    setInterval(() => PrayerTimes.render(), 5 * 60 * 1000);
});
