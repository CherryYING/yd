(function(g) {
	function b() {
		this.regional = [];
		this.regional[""] = {
			labels: ["Years", "Months", "Weeks", "Days", "Hours", "Minutes", "Seconds"],
			labels1: ["Year", "Month", "Week", "Day", "Hour", "Minute", "Second"],
			compactLabels: ["y", "m", "w", "d"],
			whichLabels: null,
			timeSeparator: ":",
			isRTL: false
		};
		this._defaults = {
			until: null,
			since: null,
			timezone: null,
			serverSync: null,
			format: "dHMS",
			layout: "",
			compact: false,
			significant: 0,
			description: "",
			expiryUrl: "",
			expiryText: "",
			alwaysExpire: false,
			onExpiry: null,
			onTick: null,
			tickInterval: 1
		};
		g.extend(this._defaults, this.regional[""]);
		this._serverSyncs = []
	}
	var j = "countdown";
	var c = 0;
	var h = 1;
	var d = 2;
	var a = 3;
	var k = 4;
	var i = 5;
	var f = 6;
	g.extend(b.prototype, {
		markerClassName: "hasCountdown",
		_timer: setInterval(function() {
			g.countdown._updateTargets()
		},
		980),
		_timerTargets: [],
		setDefaults: function(l) {
			this._resetExtraLabels(this._defaults, l);
			e(this._defaults, l || {})
		},
		UTCDate: function(t, s, r, p, o, n, m, l) {
			if (typeof s == "object" && s.constructor == Date) {
				l = s.getMilliseconds();
				m = s.getSeconds();
				n = s.getMinutes();
				o = s.getHours();
				p = s.getDate();
				r = s.getMonth();
				s = s.getFullYear()
			}
			var q = new Date();
			q.setUTCFullYear(s);
			q.setUTCDate(1);
			q.setUTCMonth(r || 0);
			q.setUTCDate(p || 1);
			q.setUTCHours(o || 0);
			q.setUTCMinutes((n || 0) - (Math.abs(t) < 30 ? t * 60 : t));
			q.setUTCSeconds(m || 0);
			q.setUTCMilliseconds(l || 0);
			return q
		},
		periodsToSeconds: function(l) {
			return l[0] * 31557600 + l[1] * 2629800 + l[2] * 604800 + l[3] * 86400 + l[4] * 3600 + l[5] * 60 + l[6]
		},
		_settingsCountdown: function(m, l) {
			if (!l) {
				return g.countdown._defaults
			}
			var n = g.data(m, j);
			return (l == "all" ? n.options: n.options[l])
		},
		_attachCountdown: function(m, l) {
			var o = g(m);
			if (o.hasClass(this.markerClassName)) {
				return
			}
			o.addClass(this.markerClassName);
			var n = {
				options: g.extend({},
				l),
				_periods: [0, 0, 0, 0, 0, 0, 0]
			};
			g.data(m, j, n);
			this._changeCountdown(m)
		},
		_addTarget: function(l) {
			if (!this._hasTarget(l)) {
				this._timerTargets.push(l)
			}
		},
		_hasTarget: function(l) {
			return (g.inArray(l, this._timerTargets) > -1)
		},
		_removeTarget: function(l) {
			this._timerTargets = g.map(this._timerTargets,
			function(m) {
				return (m == l ? null: m)
			})
		},
		_updateTargets: function() {
			for (var l = this._timerTargets.length - 1; l >= 0; l--) {
				this._updateCountdown(this._timerTargets[l])
			}
		},
		_updateCountdown: function(v, u) {
			var t = g(v);
			u = u || g.data(v, j);
			if (!u) {
				return
			}
			t.html(this._generateHTML(u));
			t[(this._get(u, "isRTL") ? "add": "remove") + "Class"]("countdown_rtl");
			var s = this._get(u, "onTick");
			if (s) {
				var r = u._hold != "lap" ? u._periods: this._calculatePeriods(u, u._show, this._get(u, "significant"), new Date());
				var q = this._get(u, "tickInterval");
				if (q == 1 || this.periodsToSeconds(r) % q == 0) {
					s.apply(v, [r])
				}
			}
			var p = u._hold != "pause" && (u._since ? u._now.getTime() < u._since.getTime() : u._now.getTime() >= u._until.getTime());
			if (p && !u._expiring) {
				u._expiring = true;
				if (this._hasTarget(v) || this._get(u, "alwaysExpire")) {
					this._removeTarget(v);
					var o = this._get(u, "onExpiry");
					if (o) {
						o.apply(v, [])
					}
					var n = this._get(u, "expiryText");
					if (n) {
						var m = this._get(u, "layout");
						u.options.layout = n;
						this._updateCountdown(v, u);
						u.options.layout = m
					}
					var l = this._get(u, "expiryUrl");
					if (l) {
						window.location = l
					}
				}
				u._expiring = false
			} else {
				if (u._hold == "pause") {
					this._removeTarget(v)
				}
			}
			g.data(v, j, u)
		},
		_changeCountdown: function(m, l, q) {
			l = l || {};
			if (typeof l == "string") {
				var p = l;
				l = {};
				l[p] = q
			}
			var o = g.data(m, j);
			if (o) {
				this._resetExtraLabels(o.options, l);
				e(o.options, l);
				this._adjustSettings(m, o);
				g.data(m, j, o);
				var n = new Date();
				if ((o._since && o._since < n) || (o._until && o._until > n)) {
					this._addTarget(m)
				}
				this._updateCountdown(m, o)
			}
		},
		_resetExtraLabels: function(m, l) {
			var p = false;
			for (var o in l) {
				if (o != "whichLabels" && o.match(/[Ll]abels/)) {
					p = true;
					break
				}
			}
			if (p) {
				for (var o in m) {
					if (o.match(/[Ll]abels[0-9]/)) {
						m[o] = null
					}
				}
			}
		},
		_adjustSettings: function(t, s) {
			var r;
			var q = this._get(s, "serverSync");
			var p = 0;
			var o = null;
			for (var l = 0; l < this._serverSyncs.length; l++) {
				if (this._serverSyncs[l][0] == q) {
					o = this._serverSyncs[l][1];
					break
				}
			}
			if (o != null) {
				p = (q ? o: 0);
				r = new Date()
			} else {
				var n = (q ? q.apply(t, []) : null);
				r = new Date();
				p = (n ? r.getTime() - n.getTime() : 0);
				this._serverSyncs.push([q, p])
			}
			var m = this._get(s, "timezone");
			m = (m == null ? -r.getTimezoneOffset() : m);
			s._since = this._get(s, "since");
			if (s._since != null) {
				s._since = this.UTCDate(m, this._determineTime(s._since, null));
				if (s._since && p) {
					s._since.setMilliseconds(s._since.getMilliseconds() + p)
				}
			}
			s._until = this.UTCDate(m, this._determineTime(this._get(s, "until"), r));
			if (p) {
				s._until.setMilliseconds(s._until.getMilliseconds() + p)
			}
			s._show = this._determineShow(s)
		},
		_destroyCountdown: function(m) {
			var l = g(m);
			if (!l.hasClass(this.markerClassName)) {
				return
			}
			this._removeTarget(m);
			l.removeClass(this.markerClassName).empty();
			g.removeData(m, j)
		},
		_pauseCountdown: function(l) {
			this._hold(l, "pause")
		},
		_lapCountdown: function(l) {
			this._hold(l, "lap")
		},
		_resumeCountdown: function(l) {
			this._hold(l, null)
		},
		_hold: function(m, l) {
			var o = g.data(m, j);
			if (o) {
				if (o._hold == "pause" && !l) {
					o._periods = o._savePeriods;
					var n = (o._since ? "-": "+");
					o[o._since ? "_since": "_until"] = this._determineTime(n + o._periods[0] + "y" + n + o._periods[1] + "o" + n + o._periods[2] + "w" + n + o._periods[3] + "d" + n + o._periods[4] + "h" + n + o._periods[5] + "m" + n + o._periods[6] + "s");
					this._addTarget(m)
				}
				o._hold = l;
				o._savePeriods = (l == "pause" ? o._periods: null);
				g.data(m, j, o);
				this._updateCountdown(m, o)
			}
		},
		_getTimesCountdown: function(m) {
			var l = g.data(m, j);
			return (!l ? null: (!l._hold ? l._periods: this._calculatePeriods(l, l._show, this._get(l, "significant"), new Date())))
		},
		_get: function(m, l) {
			return (m.options[l] != null ? m.options[l] : g.countdown._defaults[l])
		},
		_determineTime: function(r, q) {
			var p = function(m) {
				var l = new Date();
				l.setTime(l.getTime() + m * 1000);
				return l
			};
			var t = function(z) {
				z = z.toLowerCase();
				var y = new Date();
				var x = y.getFullYear();
				var w = y.getMonth();
				var v = y.getDate();
				var u = y.getHours();
				var o = y.getMinutes();
				var n = y.getSeconds();
				var m = /([+-]?[0-9]+)\s*(s|m|h|d|w|o|y)?/g;
				var l = m.exec(z);
				while (l) {
					switch (l[2] || "s") {
					case "s":
						n += parseInt(l[1], 10);
						break;
					case "m":
						o += parseInt(l[1], 10);
						break;
					case "h":
						u += parseInt(l[1], 10);
						break;
					case "d":
						v += parseInt(l[1], 10);
						break;
					case "w":
						v += parseInt(l[1], 10) * 7;
						break;
					case "o":
						w += parseInt(l[1], 10);
						v = Math.min(v, g.countdown._getDaysInMonth(x, w));
						break;
					case "y":
						x += parseInt(l[1], 10);
						v = Math.min(v, g.countdown._getDaysInMonth(x, w));
						break
					}
					l = m.exec(z)
				}
				return new Date(x, w, v, u, o, n, 0)
			};
			var s = (r == null ? q: (typeof r == "string" ? t(r) : (typeof r == "number" ? p(r) : r)));
			if (s) {
				s.setMilliseconds(0)
			}
			return s
		},
		_getDaysInMonth: function(m, l) {
			return 32 - new Date(m, l, 32).getDate()
		},
		_normalLabels: function(l) {
			return l
		},
		_generateHTML: function(H) {
			var G = this._get(H, "significant");
			H._periods = (H._hold ? H._periods: this._calculatePeriods(H, H._show, G, new Date()));
			var F = false;
			var E = 0;
			var D = G;
			var C = g.extend({},
			H._show);
			for (var B = c; B <= f; B++) {
				F |= (H._show[B] == "?" && H._periods[B] > 0);
				C[B] = (H._show[B] == "?" && !F ? null: H._show[B]);
				E += (C[B] ? 1 : 0);
				D -= (H._periods[B] > 0 ? 1 : 0)
			}
			var A = [false, false, false, false, false, false, false];
			for (var B = f; B >= c; B--) {
				if (H._show[B]) {
					if (H._periods[B]) {
						A[B] = true
					} else {
						A[B] = D > 0;
						D--
					}
				}
			}
			var z = this._get(H, "compact");
			var y = this._get(H, "layout");
			var x = (z ? this._get(H, "compactLabels") : this._get(H, "labels"));
			var w = this._get(H, "whichLabels") || this._normalLabels;
			var v = this._get(H, "timeSeparator");
			var u = this._get(H, "description") || "";
			var t = function(m) {
				var l = g.countdown._get(H, "compactLabels" + w(H._periods[m]));
				return (C[m] ? H._periods[m] + (l ? l[m] : x[m]) + " ": "")
			};
			var s = function(m) {
				var l = g.countdown._get(H, "labels" + w(H._periods[m]));
				return ((!G && C[m]) || (G && A[m]) ? '<span class="countdown_section"><span class="countdown_amount">' + H._periods[m] + "</span><br/>" + (l ? l[m] : x[m]) + "</span>": "")
			};
			return (y ? this._buildLayout(H, C, y, z, G, A) : ((z ? '<span class="countdown_row countdown_amount' + (H._hold ? " countdown_holding": "") + '">' + t(c) + t(h) + t(d) + t(a) + (C[k] ? this._minDigits(H._periods[k], 2) : "") + (C[i] ? (C[k] ? v: "") + this._minDigits(H._periods[i], 2) : "") + (C[f] ? (C[k] || C[i] ? v: "") + this._minDigits(H._periods[f], 2) : "") : '<span class="countdown_row countdown_show' + (G || E) + (H._hold ? " countdown_holding": "") + '">' + s(c) + s(h) + s(d) + s(a) + s(k) + s(i) + s(f)) + "</span>" + (u ? '<span class="countdown_row countdown_descr">' + u + "</span>": "")))
		},
		_buildLayout: function(F, E, D, C, B, A) {
			var y = this._get(F, (C ? "compactLabels": "labels"));
			var x = this._get(F, "whichLabels") || this._normalLabels;
			var w = function(l) {
				return (g.countdown._get(F, (C ? "compactLabels": "labels") + x(F._periods[l])) || y)[l]
			};
			var v = function(m, l) {
				return Math.floor(m / l) % 10
			};
			var u = {
				desc: this._get(F, "description"),
				sep: this._get(F, "timeSeparator"),
				yl: w(c),
				yn: F._periods[c],
				ynn: this._minDigits(F._periods[c], 2),
				ynnn: this._minDigits(F._periods[c], 3),
				y1: v(F._periods[c], 1),
				y10: v(F._periods[c], 10),
				y100: v(F._periods[c], 100),
				y1000: v(F._periods[c], 1000),
				ol: w(h),
				on: F._periods[h],
				onn: this._minDigits(F._periods[h], 2),
				onnn: this._minDigits(F._periods[h], 3),
				o1: v(F._periods[h], 1),
				o10: v(F._periods[h], 10),
				o100: v(F._periods[h], 100),
				o1000: v(F._periods[h], 1000),
				wl: w(d),
				wn: F._periods[d],
				wnn: this._minDigits(F._periods[d], 2),
				wnnn: this._minDigits(F._periods[d], 3),
				w1: v(F._periods[d], 1),
				w10: v(F._periods[d], 10),
				w100: v(F._periods[d], 100),
				w1000: v(F._periods[d], 1000),
				dl: w(a),
				dn: F._periods[a],
				dnn: this._minDigits(F._periods[a], 2),
				dnnn: this._minDigits(F._periods[a], 3),
				d1: v(F._periods[a], 1),
				d10: v(F._periods[a], 10),
				d100: v(F._periods[a], 100),
				d1000: v(F._periods[a], 1000),
				hl: w(k),
				hn: F._periods[k],
				hnn: this._minDigits(F._periods[k], 2),
				hnnn: this._minDigits(F._periods[k], 3),
				h1: v(F._periods[k], 1),
				h10: v(F._periods[k], 10),
				h100: v(F._periods[k], 100),
				h1000: v(F._periods[k], 1000),
				ml: w(i),
				mn: F._periods[i],
				mnn: this._minDigits(F._periods[i], 2),
				mnnn: this._minDigits(F._periods[i], 3),
				m1: v(F._periods[i], 1),
				m10: v(F._periods[i], 10),
				m100: v(F._periods[i], 100),
				m1000: v(F._periods[i], 1000),
				sl: w(f),
				sn: F._periods[f],
				snn: this._minDigits(F._periods[f], 2),
				snnn: this._minDigits(F._periods[f], 3),
				s1: v(F._periods[f], 1),
				s10: v(F._periods[f], 10),
				s100: v(F._periods[f], 100),
				s1000: v(F._periods[f], 1000)
			};
			var t = D;
			for (var z = c; z <= f; z++) {
				var s = "yowdhms".charAt(z);
				var n = new RegExp("\\{" + s + "<\\}(.*)\\{" + s + ">\\}", "g");
				t = t.replace(n, ((!B && E[z]) || (B && A[z]) ? "$1": ""))
			}
			g.each(u,
			function(o, m) {
				var l = new RegExp("\\{" + o + "\\}", "g");
				t = t.replace(l, m)
			});
			return t
		},
		_minDigits: function(m, l) {
			m = "" + m;
			if (m.length >= l) {
				return m
			}
			m = "0000000000" + m;
			return m.substr(m.length - l)
		},
		_determineShow: function(m) {
			var l = this._get(m, "format");
			var n = [];
			n[c] = (l.match("y") ? "?": (l.match("Y") ? "!": null));
			n[h] = (l.match("o") ? "?": (l.match("O") ? "!": null));
			n[d] = (l.match("w") ? "?": (l.match("W") ? "!": null));
			n[a] = (l.match("d") ? "?": (l.match("D") ? "!": null));
			n[k] = (l.match("h") ? "?": (l.match("H") ? "!": null));
			n[i] = (l.match("m") ? "?": (l.match("M") ? "!": null));
			n[f] = (l.match("s") ? "?": (l.match("S") ? "!": null));
			return n
		},
		_calculatePeriods: function(N, M, L, K) {
			N._now = K;
			N._now.setMilliseconds(0);
			var J = new Date(N._now.getTime());
			if (N._since) {
				if (K.getTime() < N._since.getTime()) {
					N._now = K = J
				} else {
					K = N._since
				}
			} else {
				J.setTime(N._until.getTime());
				if (K.getTime() > N._until.getTime()) {
					N._now = K = J
				}
			}
			var I = [0, 0, 0, 0, 0, 0, 0];
			if (M[c] || M[h]) {
				var H = g.countdown._getDaysInMonth(K.getFullYear(), K.getMonth());
				var G = g.countdown._getDaysInMonth(J.getFullYear(), J.getMonth());
				var F = (J.getDate() == K.getDate() || (J.getDate() >= Math.min(H, G) && K.getDate() >= Math.min(H, G)));
				var E = function(l) {
					return (l.getHours() * 60 + l.getMinutes()) * 60 + l.getSeconds()
				};
				var D = Math.max(0, (J.getFullYear() - K.getFullYear()) * 12 + J.getMonth() - K.getMonth() + ((J.getDate() < K.getDate() && !F) || (F && E(J) < E(K)) ? -1 : 0));
				I[c] = (M[c] ? Math.floor(D / 12) : 0);
				I[h] = (M[h] ? D - I[c] * 12 : 0);
				K = new Date(K.getTime());
				var C = (K.getDate() == H);
				var B = g.countdown._getDaysInMonth(K.getFullYear() + I[c], K.getMonth() + I[h]);
				if (K.getDate() > B) {
					K.setDate(B)
				}
				K.setFullYear(K.getFullYear() + I[c]);
				K.setMonth(K.getMonth() + I[h]);
				if (C) {
					K.setDate(B)
				}
			}
			var A = Math.floor((J.getTime() - K.getTime()) / 1000);
			var z = function(m, l) {
				I[m] = (M[m] ? Math.floor(A / l) : 0);
				A -= I[m] * l
			};
			z(d, 604800);
			z(a, 86400);
			z(k, 3600);
			z(i, 60);
			z(f, 1);
			if (A > 0 && !N._since) {
				var y = [1, 12, 4.3482, 7, 24, 60, 60];
				var x = f;
				var w = 1;
				for (var v = f; v >= c; v--) {
					if (M[v]) {
						if (I[x] >= w) {
							I[x] = 0;
							A = 1
						}
						if (A > 0) {
							I[v]++;
							A = 0;
							x = v;
							w = 1
						}
					}
					w *= y[v]
				}
			}
			if (L) {
				for (var v = c; v <= f; v++) {
					if (L && I[v]) {
						L--
					} else {
						if (!L) {
							I[v] = 0
						}
					}
				}
			}
			return I
		}
	});
	function e(m, l) {
		g.extend(m, l);
		for (var n in l) {
			if (l[n] == null) {
				m[n] = null
			}
		}
		return m
	}
	g.fn.countdown = function(m) {
		var l = Array.prototype.slice.call(arguments, 1);
		if (m == "getTimes" || m == "settings") {
			return g.countdown["_" + m + "Countdown"].apply(g.countdown, [this[0]].concat(l))
		}
		return this.each(function() {
			if (typeof m == "string") {
				g.countdown["_" + m + "Countdown"].apply(g.countdown, [this].concat(l))
			} else {
				g.countdown._attachCountdown(this, m)
			}
		})
	};
	g.countdown = new b()
})(jQuery);
