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
                    if (loc.lat && loc.lon && loc.city) {
                        resolve(loc);
                        // Background update check could be here
                        return;
                    }
                } catch (e) { /* ignore */ }
            }

            if (!navigator.geolocation) {
                const def = { lat: this.DEFAULT_LAT, lon: this.DEFAULT_LON, city: 'Toshkent' };
                localStorage.setItem(this.LOCATION_KEY, JSON.stringify(def));
                resolve(def);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const loc = {
                        lat: pos.coords.latitude,
                        lon: pos.coords.longitude,
                        city: null
                    };

                    this.getCityName(loc.lat, loc.lon).then(city => {
                        loc.city = city || 'Toshkent';
                        localStorage.setItem(this.LOCATION_KEY, JSON.stringify(loc));
                        resolve(loc);
                    });
                },
                () => {
                    const def = { lat: this.DEFAULT_LAT, lon: this.DEFAULT_LON, city: 'Toshkent' };
                    localStorage.setItem(this.LOCATION_KEY, JSON.stringify(def));
                    resolve(def);
                },
                { timeout: 3000, maximumAge: 86400000 }
            );
        });
    },

    /** Shahar nomini olish (Reverse Geocoding) */
    async getCityName(lat, lon) {
        try {
            const geoResp = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&zoom=10`);
            const geoData = await geoResp.json();
            return geoData.address?.city || geoData.address?.town || geoData.address?.state || 'Toshkent';
        } catch (e) {
            return 'Toshkent';
        }
    },

    /** Butun bir oy uchun vaqtlarni yuklash */
    async fetchMonthData(region, month, year) {
        const cacheKey = `${this.CACHE_KEY}${region}_${month}_${year}`;
        const cached = localStorage.getItem(cacheKey);

        if (cached) {
            try { return JSON.parse(cached); } catch (e) { /* ignore */ }
        }

        try {
            const resp = await fetch(`https://islomapi.uz/api/monthly?region=${region}&month=${month}`);
            const data = await resp.json();
            if (data && Array.isArray(data)) {
                localStorage.setItem(cacheKey, JSON.stringify(data));
                return data;
            }
        } catch (e) {
            console.error('IslomAPI Error:', e);
        }
        return null;
    },

    /** Bugungi namoz vaqtlarini olish (Keshdan yoki API'dan) */
    async getTodayTimes(region) {
        const today = new Date();
        const d = today.getDate();
        const m = today.getMonth() + 1;
        const y = today.getFullYear();

        const monthData = await this.fetchMonthData(region, m, y);
        if (monthData) {
            const dayData = monthData.find(item => item.date.split(',')[0].trim().includes(d.toString().padStart(2, '0')) || parseInt(item.date.split(',')[0]) === d);
            // Backup search if format varies
            const target = dayData || monthData[d - 1];

            if (target && target.times) {
                return {
                    Fajr: target.times.tong_saharlik,
                    Sunrise: target.times.quyosh,
                    Dhuhr: target.times.peshin,
                    Asr: target.times.asr,
                    Maghrib: target.times.shom_iftor,
                    Isha: target.times.hufton
                };
            }
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
        const elMobile = document.querySelector('#navLocationMobile .loc-text');
        if (elMobile) elMobile.textContent = city;
    },

    /** UI'ni yangilash */
    async render() {
        const container = document.getElementById('prayerTimesWidget');
        const loc = await this.getLocation();

        // Navbar sync
        this.updateNavbarLocation(loc.city);

        if (!container) return;

        const times = await this.getTodayTimes(loc.city);
        if (!times) {
            container.innerHTML = `<div class="text-center p-3"><i class="ri-wifi-off-line"></i> Xatolik yuz berdi</div>`;
            return;
        }

        const nextPrayer = this.getNextPrayer(times);

        // Dashboard suhoor/iftar slots
        const suhoorEl = document.getElementById('suhoorTime');
        const iftarEl = document.getElementById('iftarTime');
        if (suhoorEl) suhoorEl.textContent = times.Fajr;
        if (iftarEl) iftarEl.textContent = times.Maghrib;

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

        container.innerHTML = `
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;padding:0 4px;">
                <i class="ri-map-pin-2-fill" style="color:var(--accent);font-size:0.9rem;"></i>
                <span style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);">${loc.city} shahri vaqti</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">${prayerRows}</div>
        `;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    PrayerTimes.render();
    setInterval(() => PrayerTimes.render(), 60000); // Har daqiqada tekshirish
});
