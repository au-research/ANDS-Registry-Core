(function($) {
/**
 * @license
 * =========================================================
 * ands-datetimepicker.js
 * http://tarruda.github.io/bootstrap-datetimepicker
 * http://www.eyecon.ro/bootstrap-datepicker
 * =========================================================
 * Copyright 2013 Australian National Data Service (ANDS)
 *
 * (Copyright 2012 Stefan Petre)
 *
 * (Contributions:
 *  - Andrew Rowls
 *  - Thiago de Arruda)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =========================================================
 */

  /**
   * https://github.com/sproutsocial/walltime-js: walltime-data.min.js;
   * non-essential zone and rule information pruned
   *
   * WallTime License:
   * The MIT License
   *
   * Copyright (c) 2013 Sprout Social, Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a
   * copy of this software and associated documentation files (the "Software"),
   * to deal in the Software without restriction, including without limitation
   * the rights to use, copy, modify, merge, publish, distribute, sublicense,
   * and/or sell copies of the Software, and to permit persons to whom the
   * Software is furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
   * SOFTWARE.
   */
(function(){window.WallTime||(window.WallTime={}),window.WallTime.data={rules:{Namibia:[{name:"Namibia",_from:"1994",_to:"max",type:"-","in":"Sep",on:"Sun>=1",at:"2:00",_save:"1:00",letter:"S"},{name:"Namibia",_from:"1995",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"2:00",_save:"0",letter:"-"}],SA:[{name:"SA",_from:"1942",_to:"1943",type:"-","in":"Sep",on:"Sun>=15",at:"2:00",_save:"1:00",letter:"-"},{name:"SA",_from:"1943",_to:"1944",type:"-","in":"Mar",on:"Sun>=15",at:"2:00",_save:"0",letter:"-"}],Azer:[{name:"Azer",_from:"1997",_to:"max",type:"-","in":"Mar",on:"lastSun",at:"4:00",_save:"1:00",letter:"S"},{name:"Azer",_from:"1997",_to:"max",type:"-","in":"Oct",on:"lastSun",at:"5:00",_save:"0",letter:"-"}],Dhaka:[{name:"Dhaka",_from:"2009",_to:"only",type:"-","in":"Jun",on:"19",at:"23:00",_save:"1:00",letter:"S"},{name:"Dhaka",_from:"2009",_to:"only",type:"-","in":"Dec",on:"31",at:"23:59",_save:"0",letter:"-"}],Iran:[{name:"Iran",_from:"2013",_to:"2015",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2013",_to:"2015",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2016",_to:"only",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2016",_to:"only",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2017",_to:"2019",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2017",_to:"2019",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2020",_to:"only",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2020",_to:"only",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2021",_to:"2023",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2021",_to:"2023",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2024",_to:"only",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2024",_to:"only",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2025",_to:"2027",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2025",_to:"2027",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2028",_to:"2029",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2028",_to:"2029",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2030",_to:"2031",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2030",_to:"2031",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2032",_to:"2033",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2032",_to:"2033",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2034",_to:"2035",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2034",_to:"2035",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2036",_to:"2037",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2036",_to:"2037",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"}],AN:[{name:"AN",_from:"2008",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"2:00s",_save:"0",letter:"-"},{name:"AN",_from:"2008",_to:"max",type:"-","in":"Oct",on:"Sun>=1",at:"2:00s",_save:"1:00",letter:"-"}],LH:[{name:"LH",_from:"2008",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"2:00",_save:"0",letter:"-"},{name:"LH",_from:"2008",_to:"max",type:"-","in":"Oct",on:"Sun>=1",at:"2:00",_save:"0:30",letter:"-"}],NZ:[{name:"NZ",_from:"2007",_to:"max",type:"-","in":"Sep",on:"lastSun",at:"2:00s",_save:"1:00",letter:"D"},{name:"NZ",_from:"2008",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"2:00s",_save:"0",letter:"S"}],Chatham:[{name:"Chatham",_from:"2007",_to:"max",type:"-","in":"Sep",on:"lastSun",at:"2:45s",_save:"1:00",letter:"D"},{name:"Chatham",_from:"2008",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"2:45s",_save:"0",letter:"S"}],WS:[{name:"WS",_from:"2012",_to:"max",type:"-","in":"Sep",on:"lastSun",at:"3:00",_save:"1",letter:"D"},{name:"WS",_from:"2012",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"4:00",_save:"0",letter:"-"}],EU:[{name:"EU",_from:"1981",_to:"max",type:"-","in":"Mar",on:"lastSun",at:"1:00u",_save:"1:00",letter:"S"},{name:"EU",_from:"1996",_to:"max",type:"-","in":"Oct",on:"lastSun",at:"1:00u",_save:"0",letter:"-"}],US:[{name:"US",_from:"2007",_to:"max",type:"-","in":"Mar",on:"Sun>=8",at:"2:00",_save:"1:00",letter:"D"},{name:"US",_from:"2007",_to:"max",type:"-","in":"Nov",on:"Sun>=1",at:"2:00",_save:"0",letter:"S"}],Canada:[{name:"Canada",_from:"2007",_to:"max",type:"-","in":"Mar",on:"Sun>=8",at:"2:00",_save:"1:00",letter:"D"},{name:"Canada",_from:"2007",_to:"max",type:"-","in":"Nov",on:"Sun>=1",at:"2:00",_save:"0",letter:"S"}],Chile:[{name:"Chile",_from:"2008",_to:"only",type:"-","in":"Mar",on:"30",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2009",_to:"only",type:"-","in":"Mar",on:"Sun>=9",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2010",_to:"only",type:"-","in":"Apr",on:"Sun>=1",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2011",_to:"only",type:"-","in":"May",on:"Sun>=2",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2011",_to:"only",type:"-","in":"Aug",on:"Sun>=16",at:"4:00u",_save:"1:00",letter:"S"},{name:"Chile",_from:"2012",_to:"only",type:"-","in":"Apr",on:"Sun>=23",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2012",_to:"only",type:"-","in":"Sep",on:"Sun>=2",at:"4:00u",_save:"1:00",letter:"S"},{name:"Chile",_from:"2013",_to:"max",type:"-","in":"Mar",on:"Sun>=9",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2013",_to:"max",type:"-","in":"Oct",on:"Sun>=9",at:"4:00u",_save:"1:00",letter:"S"}],Uruguay:[{name:"Uruguay",_from:"2006",_to:"max",type:"-","in":"Oct",on:"Sun>=1",at:"2:00",_save:"1:00",letter:"S"},{name:"Uruguay",_from:"2007",_to:"max",type:"-","in":"Mar",on:"Sun>=8",at:"2:00",_save:"0",letter:"-"}]},zones:{"Pacific/Pago_Pago":[{name:"Pacific/Pago_Pago",_offset:"-11:00",_rule:"-",format:"SST",_until:""}],"America/Adak":[{name:"America/Adak",_offset:"-10:00",_rule:"US",format:"HA%sT",_until:""}],"Pacific/Honolulu":[{name:"Pacific/Honolulu",_offset:"-10:00",_rule:"-",format:"HST",_until:""}],"Pacific/Marquesas":[{name:"Pacific/Marquesas",_offset:"-9:30",_rule:"-",format:"MART",_until:""}],"America/Anchorage":[{name:"America/Anchorage",_offset:"-9:00",_rule:"US",format:"AK%sT",_until:""}],"Pacific/Gambier":[{name:"Pacific/Gambier",_offset:"-9:00",_rule:"-",format:"GAMT",_until:""}],"America/Los_Angeles":[{name:"America/Los_Angeles",_offset:"-8:00",_rule:"US",format:"P%sT",_until:""}],"Pacific/Pitcairn":[{name:"Pacific/Pitcairn",_offset:"-8:00",_rule:"-",format:"PST",_until:""}],"America/Denver":[{name:"America/Denver",_offset:"-7:00",_rule:"US",format:"M%sT",_until:""}],"America/Phoenix":[{name:"America/Phoenix",_offset:"-7:00",_rule:"-",format:"MST",_until:""}],"America/Chicago":[{name:"America/Chicago",_offset:"-6:00",_rule:"US",format:"C%sT",_until:""}],"America/Guatemala":[{name:"America/Guatemala",_offset:"-6:00",_rule:"Guat",format:"C%sT",_until:""}],"Pacific/Easter":[{name:"Pacific/Easter",_offset:"-6:00",_rule:"Chile",format:"EAS%sT",_until:""}],"America/Bogota":[{name:"America/Bogota",_offset:"-5:00",_rule:"CO",format:"CO%sT",_until:""}],"America/New_York":[{name:"America/New_York",_offset:"-5:00",_rule:"US",format:"E%sT",_until:""}],"America/Caracas":[{name:"America/Caracas",_offset:"-4:30",_rule:"-",format:"VET",_until:""}],"America/Halifax":[{name:"America/Halifax",_offset:"-4:00",_rule:"Canada",format:"A%sT",_until:""}],"America/Santiago":[{name:"America/Santiago",_offset:"-4:00",_rule:"Chile",format:"CL%sT",_until:""}],"America/Santo_Domingo":[{name:"America/Santo_Domingo",_offset:"-4:00",_rule:"-",format:"AST",_until:""}],"America/St_Johns":[{name:"America/St_Johns",_offset:"-3:30",_rule:"Canada",format:"N%sT",_until:""}],"America/Godthab":[{name:"America/Godthab",_offset:"-3:00",_rule:"EU",format:"WG%sT",_until:""}],"America/Montevideo":[{name:"America/Montevideo",_offset:"-3:00",_rule:"Uruguay",format:"UY%sT",_until:""}],"America/Argentina/Buenos_Aires":[{name:"America/Argentina/Buenos_Aires",_offset:"-3:00",_rule:"Arg",format:"AR%sT",_until:""}],"America/Noronha":[{name:"America/Noronha",_offset:"-2:00",_rule:"-",format:"FNT",_until:""}],"Atlantic/Azores":[{name:"Atlantic/Azores",_offset:"-1:00",_rule:"EU",format:"AZO%sT",_until:""}],"Atlantic/Cape_Verde":[{name:"Atlantic/Cape_Verde",_offset:"-1:00",_rule:"-",format:"CVT",_until:""}],UTC:[{name:"UTC",_offset:"0:00",_rule:"-",format:"UTC",_until:""}],"Europe/London":[{name:"Europe/London",_offset:"0:00",_rule:"EU",format:"GMT/BST",_until:""}],"Africa/Windhoek":[{name:"Africa/Windhoek",_offset:"1:00",_rule:"Namibia",format:"WA%sT",_until:""}],"Europe/Berlin":[{name:"Europe/Berlin",_offset:"1:00",_rule:"EU",format:"CE%sT",_until:""}],"Africa/Johannesburg":[{name:"Africa/Johannesburg",_offset:"2:00",_rule:"SA",format:"SAST",_until:""}],"Asia/Beirut":[{name:"Asia/Beirut",_offset:"2:00",_rule:"Lebanon",format:"EE%sT",_until:""}],"Asia/Baghdad":[{name:"Asia/Baghdad",_offset:"3:00",_rule:"Iraq",format:"A%sT",_until:""}],"Asia/Tehran":[{name:"Asia/Tehran",_offset:"3:30",_rule:"Iran",format:"IR%sT",_until:""}],"Asia/Baku":[{name:"Asia/Baku",_offset:"4:00",_rule:"Azer",format:"AZ%sT",_until:""}],"Asia/Dubai":[{name:"Asia/Dubai",_offset:"4:00",_rule:"-",format:"GST",_until:""}],"Europe/Moscow":[{name:"Europe/Moscow",_offset:"4:00",_rule:"-",format:"MSK",_until:""}],"Asia/Kabul":[{name:"Asia/Kabul",_offset:"4:30",_rule:"-",format:"AFT",_until:""}],"Asia/Karachi":[{name:"Asia/Karachi",_offset:"5:00",_rule:"Pakistan",format:"PK%sT",_until:""}],"Asia/Kolkata":[{name:"Asia/Kolkata",_offset:"5:30",_rule:"-",format:"IST",_until:""}],"Asia/Kathmandu":[{name:"Asia/Kathmandu",_offset:"5:45",_rule:"-",format:"NPT",_until:""}],"Asia/Dhaka":[{name:"Asia/Dhaka",_offset:"6:00",_rule:"Dhaka",format:"BD%sT",_until:""}],"Asia/Yekaterinburg":[{name:"Asia/Yekaterinburg",_offset:"6:00",_rule:"-",format:"YEKT",_until:""}],"Asia/Rangoon":[{name:"Asia/Rangoon",_offset:"6:30",_rule:"-",format:"MMT",_until:""}],"Asia/Omsk":[{name:"Asia/Omsk",_offset:"7:00",_rule:"-",format:"OMST",_until:""}],"Asia/Shanghai":[{name:"Asia/Shanghai",_offset:"8:00",_rule:"PRC",format:"C%sT",_until:""}],"Asia/Krasnoyarsk":[{name:"Asia/Krasnoyarsk",_offset:"8:00",_rule:"-",format:"KRAT",_until:""}],"Australia/Darwin":[{name:"Australia/Darwin",_offset:"9:30",_rule:"Aus",format:"CST",_until:""}],"Australia/Perth":[{name:"Australia/Perth",_offset:"8:00",_rule:"AW",format:"WST",_until:""}],"Australia/Eucla":[{name:"Australia/Eucla",_offset:"8:45",_rule:"AW",format:"CWST",_until:""}],"Australia/Brisbane":[{name:"Australia/Lindeman",_offset:"10:00",_rule:"Holiday",format:"EST",_until:""}],"Australia/Adelaide":[{name:"Australia/Adelaide",_offset:"9:30",_rule:"AS",format:"CST",_until:""}],"Australia/Hobart":[{name:"Australia/Hobart",_offset:"10:00",_rule:"AT",format:"EST",_until:""}],"Australia/Currie":[{name:"Australia/Currie",_offset:"10:00",_rule:"AT",format:"EST",_until:""}],"Australia/Melbourne":[{name:"Australia/Melbourne",_offset:"10:00",_rule:"AV",format:"EST",_until:""}],"Australia/Sydney":[{name:"Australia/Sydney",_offset:"10:00",_rule:"AN",format:"EST",_until:""}],"Australia/Broken_Hill":[{name:"Australia/Broken_Hill",_offset:"9:30",_rule:"AS",format:"CST",_until:""}],"Australia/Lord_Howe":[{name:"Australia/Lord_Howe",_offset:"10:30",_rule:"LH",format:"LHST",_until:""}],"Asia/Irkutsk":[{name:"Asia/Irkutsk",_offset:"9:00",_rule:"-",format:"IRKT",_until:""}],"Asia/Tokyo":[{name:"Asia/Tokyo",_offset:"9:00",_rule:"Japan",format:"J%sT",_until:""}],"Asia/Yakutsk":[{name:"Asia/Yakutsk",_offset:"10:00",_rule:"-",format:"YAKT",_until:""}],"Asia/Vladivostok":[{name:"Asia/Vladivostok",_offset:"11:00",_rule:"-",format:"VLAT",_until:""}],"Pacific/Noumea":[{name:"Pacific/Noumea",_offset:"11:00",_rule:"NC",format:"NC%sT",_until:""}],"Pacific/Norfolk":[{name:"Pacific/Norfolk",_offset:"11:30",_rule:"-",format:"NFT",_until:""}],"Asia/Kamchatka":[{name:"Asia/Kamchatka",_offset:"12:00",_rule:"-",format:"PETT",_until:""}],"Pacific/Auckland":[{name:"Pacific/Auckland",_offset:"12:00",_rule:"NZ",format:"NZ%sT",_until:""}],"Pacific/Majuro":[{name:"Pacific/Majuro",_offset:"12:00",_rule:"-",format:"MHT",_until:""}],"Pacific/Tarawa":[{name:"Pacific/Tarawa",_offset:"12:00",_rule:"-",format:"GILT",_until:""}],"Pacific/Chatham":[{name:"Pacific/Chatham",_offset:"12:45",_rule:"Chatham",format:"CHA%sT",_until:""}],"Pacific/Apia":[{name:"Pacific/Apia",_offset:"13:00",_rule:"WS",format:"WS%sT",_until:""}],"Pacific/Kiritimati":[{name:"Pacific/Kiritimati",_offset:"14:00",_rule:"-",format:"LINT",_until:""}]}},window.WallTime.autoinit=!0}).call(this);
    /**
     * https://github.com/sproutsocial/walltime-js: walltime.min.js
     */
(function(){var e,t,n,i,o,r;(r=Array.prototype).indexOf||(r.indexOf=function(e){var t,n,i,o
for(t=i=0,o=this.length;o>i;t=++i)if(n=this[t],n===e)return t
return-1}),e={DayShortNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],DayIndex:function(e){return this.DayShortNames.indexOf(e)},DayNameFromIndex:function(e){return this.DayShortNames[e]},AddToDate:function(e,n){return i.MakeDateFromTimeStamp(e.getTime()+n*t.inDay)}},n={MonthsShortNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],CompareRuleMatch:new RegExp("([a-zA-Z]*)([\\<\\>]?=)([0-9]*)"),MonthIndex:function(e){return this.MonthsShortNames.indexOf(e.slice(0,3))},IsDayOfMonthRule:function(e){return e.indexOf(">")>-1||e.indexOf("<")>-1||e.indexOf("=")>-1},IsLastDayOfMonthRule:function(e){return e.slice(0,4)==="last"},DayOfMonthByRule:function(e,t,n){var i,r,s,l,a,u,h,T,f
if(u=this.CompareRuleMatch.exec(e),!u)throw new Error("Unable to parse the 'on' rule for "+e)
if(f=u.slice(1,4),a=f[0],T=f[1],s=f[2],s=parseInt(s,10),0/0===s)throw new Error("Unable to parse the dateIndex of the 'on' rule for "+e)
if(l=o.Days.DayIndex(a),r={">=":function(e,t){return e>=t},"<=":function(e,t){return t>=e},">":function(e,t){return e>t},"<":function(e,t){return t>e},"=":function(e,t){return e===t}},i=r[T],!i)throw new Error("Unable to parse the conditional for "+T)
for(h=o.Time.MakeDateFromParts(t,n);l!==h.getUTCDay()||!i(h.getUTCDate(),s);)h=o.Days.AddToDate(h,1)
return h.getUTCDate()},LastDayOfMonthRule:function(e,t,n){var i,r,s
for(r=e.slice(4),i=o.Days.DayIndex(r),s=11>n?o.Time.MakeDateFromParts(t,n+1):o.Time.MakeDateFromParts(t+1,0),s=o.Days.AddToDate(s,-1);s.getUTCDay()!==i;)s=o.Days.AddToDate(s,-1)
return s.getUTCDate()}},t={inDay:864e5,inHour:36e5,inMinute:6e4,inSecond:1e3},i={Add:function(e,n,i,o){var r
return null==n&&(n=0),null==i&&(i=0),null==o&&(o=0),r=e.getTime()+n*t.inHour+i*t.inMinute+o*t.inSecond,this.MakeDateFromTimeStamp(r)},ParseGMTOffset:function(e){var t,n,i,o,r
return o=new RegExp("(-)?([0-9]*):([0-9]*):?([0-9]*)?"),i=o.exec(e),r=i?function(){var e,t,o,r
for(o=i.slice(2),r=[],e=0,t=o.length;t>e;e++)n=o[e],r.push(parseInt(n,10))
return r}():[0,0,0],t=i&&i[1]==="-",r.splice(0,0,t),r},ParseTime:function(e){var t,n,i,o,r
return o=new RegExp("(\\d*)\\:(\\d*)([wsugz]?)"),(n=o.exec(e))?(r=function(){var e,i,o,r
for(o=n.slice(1,3),r=[],e=0,i=o.length;i>e;e++)t=o[e],r.push(parseInt(t,10))
return r}(),i=n[3]?n[3]:"",r.push(i),r):[0,0,""]},ApplyOffset:function(e,n,i){var o
return o=t.inHour*n.hours+t.inMinute*n.mins+t.inSecond*n.secs,n.negative||(o=-1*o),i&&(o=-1*o),this.MakeDateFromTimeStamp(e.getTime()+o)},ApplySave:function(e,t,n){return n!==!0&&(n=!1),this.ApplyOffset(e,{negative:!0,hours:t.hours,mins:t.mins,secs:0},n)},UTCToWallTime:function(e,t,n){var i
return i=this.UTCToStandardTime(e,t),this.ApplySave(i,n)},UTCToStandardTime:function(e,t){return this.ApplyOffset(e,t,!0)},UTCToQualifiedTime:function(e,t,n,i){var o
switch(o=e,t){case"w":o=this.UTCToWallTime(o,n,i())
break
case"s":o=this.UTCToStandardTime(o,n)}return o},QualifiedTimeToUTC:function(e,t,n,i){var o
switch(o=e,t){case"w":o=this.WallTimeToUTC(n,i(),o)
break
case"s":o=this.StandardTimeToUTC(n,o)}return o},StandardTimeToUTC:function(e,t,n,i,o,r,s,l){var a
return null==n&&(n=0),null==i&&(i=1),null==o&&(o=0),null==r&&(r=0),null==s&&(s=0),null==l&&(l=0),a="number"==typeof t?this.MakeDateFromParts(t,n,i,o,r,s,l):t,this.ApplyOffset(a,e)},WallTimeToUTC:function(e,t,n,i,o,r,s,l,a){var u
return null==i&&(i=0),null==o&&(o=1),null==r&&(r=0),null==s&&(s=0),null==l&&(l=0),null==a&&(a=0),u=this.StandardTimeToUTC(e,n,i,o,r,s,l,a),this.ApplySave(u,t,!0)},MakeDateFromParts:function(e,t,n,i,o,r,s){var l
return null==t&&(t=0),null==n&&(n=1),null==i&&(i=0),null==o&&(o=0),null==r&&(r=0),null==s&&(s=0),Date.UTC?new Date(Date.UTC(e,t,n,i,o,r,s)):(l=new Date,l.setUTCFullYear(e),l.setUTCMonth(t),l.setUTCDate(n),l.setUTCHours(i),l.setUTCMinutes(o),l.setUTCSeconds(r),l.setUTCMilliseconds(s),l)},LocalDate:function(e,t,n,i,o,r,s,l,a){return null==i&&(i=0),null==o&&(o=1),null==r&&(r=0),null==s&&(s=0),null==l&&(l=0),null==a&&(a=0),this.WallTimeToUTC(e,t,n,i,o,r,s,l,a)},MakeDateFromTimeStamp:function(e){return new Date(e)},MaxDate:function(){return this.MakeDateFromTimeStamp(864e12)},MinDate:function(){return this.MakeDateFromTimeStamp(-864e12)}},o={Days:e,Months:n,Milliseconds:t,Time:i,noSave:{hours:0,mins:0},noZone:{offset:{negative:!1,hours:0,mins:0,secs:0},name:"UTC"}},"undefined"==typeof window?module.exports=o:"undefined"!=typeof define?define("olson/helpers",[],o):(this.WallTime||(this.WallTime={}),this.WallTime.helpers=o)}).call(this),function(){var e,t
e=function(e){var t
return t=function(){function t(t,n,i){this.utc=t,this.zone=n,this.save=i,this.offset=this.zone.offset,this.wallTime=e.Time.UTCToWallTime(this.utc,this.offset,this.save)}return t.prototype.getFullYear=function(){return this.wallTime.getUTCFullYear()},t.prototype.getMonth=function(){return this.wallTime.getUTCMonth()},t.prototype.getDate=function(){return this.wallTime.getUTCDate()},t.prototype.getDay=function(){return this.wallTime.getUTCDay()},t.prototype.getHours=function(){return this.wallTime.getUTCHours()},t.prototype.getMinutes=function(){return this.wallTime.getUTCMinutes()},t.prototype.getSeconds=function(){return this.wallTime.getUTCSeconds()},t.prototype.getMilliseconds=function(){return this.wallTime.getUTCMilliseconds()},t.prototype.getUTCFullYear=function(){return this.utc.getUTCFullYear()},t.prototype.getUTCMonth=function(){return this.utc.getUTCMonth()},t.prototype.getUTCDate=function(){return this.utc.getUTCDate()},t.prototype.getUTCDay=function(){return this.utc.getUTCDay()},t.prototype.getUTCHours=function(){return this.utc.getUTCHours()},t.prototype.getUTCMinutes=function(){return this.utc.getUTCMinutes()},t.prototype.getUTCSeconds=function(){return this.utc.getUTCSeconds()},t.prototype.getUTCMilliseconds=function(){return this.utc.getUTCMilliseconds()},t.prototype.getTime=function(){return this.utc.getTime()},t.prototype.getTimezoneOffset=function(){var e,t
return e=this.offset.hours*60+this.offset.mins,t=this.save.hours*60+this.save.mins,this.offset.negative||(e=-e),e-t},t.prototype.toISOString=function(){return this.wallTime.toISOString()},t.prototype.toUTCString=function(){return this.wallTime.toUTCString()},t.prototype.toDateString=function(){var e,t
return t=this.wallTime.toUTCString(),e=t.match("([a-zA-Z]*), ([0-9]+) ([a-zA-Z]*) ([0-9]+)"),[e[1],e[3],e[2],e[4]].join(" ")},t.prototype.toFormattedTime=function(e){var t,n,i,o
return null==e&&(e=!1),t=o=this.getHours(),t>12&&!e&&(t-=12),0===t&&(t=12),i=this.getMinutes(),10>i&&(i="0"+i),n=o>11?" PM":" AM",e&&(n=""),""+t+":"+i+n},t.prototype.setTime=function(t){return this.wallTime=e.Time.UTCToWallTime(new Date(t),this.zone.offset,this.save),this._updateUTC()},t.prototype.setFullYear=function(e){return this.wallTime.setUTCFullYear(e),this._updateUTC()},t.prototype.setMonth=function(e){return this.wallTime.setUTCMonth(e),this._updateUTC()},t.prototype.setDate=function(e){return this.wallTime.setUTCDate(e),this._updateUTC()},t.prototype.setHours=function(e){return this.wallTime.setUTCHours(e),this._updateUTC()},t.prototype.setMinutes=function(e){return this.wallTime.setUTCMinutes(e),this._updateUTC()},t.prototype.setSeconds=function(e){return this.wallTime.setUTCSeconds(e),this._updateUTC()},t.prototype.setMilliseconds=function(e){return this.wallTime.setUTCMilliseconds(e),this._updateUTC()},t.prototype._updateUTC=function(){return this.utc=e.Time.WallTimeToUTC(this.offset,this.save,this.getFullYear(),this.getMonth(),this.getDate(),this.getHours(),this.getMinutes(),this.getSeconds(),this.getMilliseconds()),this.utc.getTime()},t}()},"undefined"==typeof window?(t=require("./helpers"),module.exports=e(t)):"undefined"!=typeof define?define("olson/timezonetime",["olson/helpers"],e):(this.WallTime||(this.WallTime={}),this.WallTime.TimeZoneTime=e(this.WallTime.helpers))}.call(this),function(){var e,t,n,i={}.hasOwnProperty
e=function(e,t){var n,o,r,s,l,a
return r=function(){function e(){}return e.prototype.applies=function(e){return!isNaN(parseInt(e,10))},e.prototype.parseDate=function(e){return parseInt(e,10)},e}(),o=function(){function t(){}return t.prototype.applies=e.Months.IsLastDayOfMonthRule,t.prototype.parseDate=function(t,n,i){return e.Months.LastDayOfMonthRule(t,n,i)},t}(),n=function(){function t(){}return t.prototype.applies=e.Months.IsDayOfMonthRule,t.prototype.parseDate=function(t,n,i){return e.Months.DayOfMonthByRule(t,n,i)},t}(),s=function(){function t(t,n,i,o,r,s,l,a,u){var h,T,f,m
switch(this.name=t,this._from=n,this._to=i,this.type=o,this["in"]=r,this.on=s,this.at=l,this._save=a,this.letter=u,this.from=parseInt(this._from,10),this.isMax=!1,f=this.from,this._to){case"max":f=e.Time.MaxDate().getUTCFullYear(),this.isMax=!0
break
case"only":f=this.from
break
default:f=parseInt(this._to,10)}this.to=f,m=this._parseTime(this._save),h=m[0],T=m[1],this.save={hours:h,mins:T}}return t.prototype.forZone=function(t){return this.offset=t,this.fromUTC=e.Time.MakeDateFromParts(this.from,0,1,0,0,0),this.fromUTC=e.Time.ApplyOffset(this.fromUTC,t),this.toUTC=e.Time.MakeDateFromParts(this.to,11,31,23,59,59,999),this.toUTC=e.Time.ApplyOffset(this.toUTC,t)},t.prototype.setOnUTC=function(t,n,i){var o,r,s,l,a,u,h,T=this
return u=e.Months.MonthIndex(this["in"]),r=parseInt(this.on,10),s=isNaN(r)?this._parseOnDay(this.on,t,u):r,h=this._parseTime(this.at),l=h[0],a=h[1],o=h[2],this.onUTC=e.Time.MakeDateFromParts(t,u,s,l,a),this.onUTC.setUTCMilliseconds(this.onUTC.getUTCMilliseconds()-1),this.atQualifier=""!==o?o:"w",this.onUTC=e.Time.QualifiedTimeToUTC(this.onUTC,this.atQualifier,n,function(){return i(T)}),this.onSort=""+u+"-"+s+"-"+this.onUTC.getUTCHours()+"-"+this.onUTC.getUTCMinutes()},t.prototype.appliesToUTC=function(e){return this.fromUTC<=e&&e<=this.toUTC},t.prototype._parseOnDay=function(e,t,i){var s,l,a,u
for(l=[new r,new o,new n],a=0,u=l.length;u>a;a++)if(s=l[a],s.applies(e))return s.parseDate(e,t,i)
throw new Error("Unable to parse 'on' field for "+this.name+"|"+this._from+"|"+this._to+"|"+e)},t.prototype._parseTime=function(t){return e.Time.ParseTime(t)},t}(),l=function(){function n(t,n){var o,r,s,l,a,u,h,T,f,m=this
if(this.rules=t,this.timeZone=n,a=null,l=null,s={},o={},f=this.rules,"undefined"!=typeof f)for(h=0,T=f.length;T>h;h++)u=f[h],u.forZone(this.timeZone.offset,function(){return e.noSave}),(null===a||u.from<a)&&(a=u.from),(null===l||u.to>l)&&(l=u.to),s[u.to]=s[u.to]||[],s[u.to].push(u),o[u.from]=o[u.from]||[],o[u.from].push(u)
this.minYear=a,this.maxYear=l,r=function(n,o){var r,l,a,h
null==n&&(n="toUTC"),null==o&&(o=s),h=[]
for(l in o)i.call(o,l)&&(t=o[l],a=m.allThatAppliesTo(t[0][n]),a.length<1||(t=m._sortRulesByOnTime(t),r=a.slice(-1)[0],(r.save.hours!==0||r.save.mins!==0)&&h.push(function(){var i,o,s
for(s=[],i=0,o=t.length;o>i;i++)u=t[i],s.push(u[n]=e.Time.ApplySave(u[n],r.save))
return s}())))
return h},r("toUTC",s),r("fromUTC",o)}return n.prototype.allThatAppliesTo=function(e){var t,n,i,o,r
if(o=this.rules,r=[],"undefined"!=typeof o)for(n=0,i=o.length;i>n;n++)t=o[n],t.appliesToUTC(e)&&r.push(t)
return r},n.prototype.getWallTimeForUTC=function(n){var i,o,r,s,l,a,u
if(l=this.allThatAppliesTo(n),l.length<1)return new t(n,this.timeZone,e.noSave)
for(l=this._sortRulesByOnTime(l),o=function(t){var n
return n=l.indexOf(t),1>n?l.length<1?e.noSave:l.slice(-1)[0].save:l[n-1].save},a=0,u=l.length;u>a;a++)s=l[a],s.setOnUTC(n.getUTCFullYear(),this.timeZone.offset,o)
return i=function(){var e,t,i
for(i=[],e=0,t=l.length;t>e;e++)s=l[e],s.onUTC.getTime()<n.getTime()&&i.push(s)
return i}(),r=l.length<1?e.noSave:l.slice(-1)[0].save,i.length>0&&(r=i.slice(-1)[0].save),new t(n,this.timeZone,r)},n.prototype.getUTCForWallTime=function(t){var n,i,o,r,s,l,a,u
if(l=e.Time.StandardTimeToUTC(this.timeZone.offset,t),s=function(){var e,t,n,i
if(n=this.rules,i=[],"undefined"!=typeof n)for(e=0,t=n.length;t>e;e++)r=n[e],r.appliesToUTC(l)&&i.push(r)
return i}.call(this),s.length<1)return l
for(s=this._sortRulesByOnTime(s),i=function(t){var n
return n=s.indexOf(t),1>n?s.length<1?e.noSave:s.slice(-1)[0].save:s[n-1].save},a=0,u=s.length;u>a;a++)r=s[a],r.setOnUTC(l.getUTCFullYear(),this.timeZone.offset,i)
return n=function(){var e,t,n
for(n=[],e=0,t=s.length;t>e;e++)r=s[e],r.onUTC.getTime()<l.getTime()&&n.push(r)
return n}(),o=s.length<1?e.noSave:s.slice(-1)[0].save,n.length>0&&(o=n.slice(-1)[0].save),e.Time.WallTimeToUTC(this.timeZone.offset,o,t)},n.prototype.getYearEndDST=function(t){var n,i,o,r,s,l,a,u,h
if(a=typeof t===number?t:t.getUTCFullYear(),l=e.Time.StandardTimeToUTC(this.timeZone.offset,a,11,31,23,59,59),s=function(){var e,t,n,i
if(n=this.rules,i=[],"undefined"!=typeof n)for(e=0,t=n.length;t>e;e++)r=n[e],r.appliesToUTC(l)&&i.push(r)
return i}.call(this),s.length<1)return e.noSave
for(s=this._sortRulesByOnTime(s),i=function(t){var n
return n=s.indexOf(t),1>n?e.noSave:s[n-1].save},u=0,h=s.length;h>u;u++)r=s[u],r.setOnUTC(l.getUTCFullYear(),this.timeZone.offset,i)
return n=function(){var e,t,n
for(n=[],e=0,t=s.length;t>e;e++)r=s[e],r.onUTC.getTime()<l.getTime()&&n.push(r)
return n}(),o=e.noSave,n.length>0&&(o=n.slice(-1)[0].save),o},n.prototype.isAmbiguous=function(t){var n,i,o,r,s,l,a,u,h,T,f,m,p,c
if(m=e.Time.StandardTimeToUTC(this.timeZone.offset,t),h=function(){var e,t,n,i
if(n=this.rules,i=[],"undefined"!=typeof n)for(e=0,t=n.length;t>e;e++)u=n[e],u.appliesToUTC(m)&&i.push(u)
return i}.call(this),h.length<1)return!1
for(h=this._sortRulesByOnTime(h),i=function(t){var n
return n=h.indexOf(t),1>n?e.noSave:h[n-1].save},p=0,c=h.length;c>p;p++)u=h[p],u.setOnUTC(m.getUTCFullYear(),this.timeZone.offset,i)
return n=function(){var e,t,n
for(n=[],e=0,t=h.length;t>e;e++)u=h[e],u.onUTC.getTime()<=m.getTime()-1&&n.push(u)
return n}(),n.length<1?!1:(o=n.slice(-1)[0],l=i(o),f={prev:l.hours*60+l.mins,last:o.save.hours*60+o.save.mins},f.prev===f.last?!1:(T=f.prev<f.last,r=function(t,n){var i,o
return i={begin:e.Time.MakeDateFromTimeStamp(t.getTime()+1)},i.end=e.Time.Add(i.begin,0,n),i.begin.getTime()>i.end.getTime()&&(o=i.begin,i.begin=i.end,i.end=o),i},s=T?f.last:-f.prev,a=r(o.onUTC,s),m=e.Time.WallTimeToUTC(this.timeZone.offset,l,t),a.begin<=m&&m<=a.end))},n.prototype._sortRulesByOnTime=function(t){return t.sort(function(t,n){return e.Months.MonthIndex(t["in"])-e.Months.MonthIndex(n["in"])})},n}(),a={Rule:s,RuleSet:l,OnFieldHandlers:{NumberHandler:r,LastHandler:o,CompareHandler:n}}},"undefined"==typeof window?(n=require("./helpers"),t=require("./timezonetime"),module.exports=e(n,t)):"undefined"!=typeof define?define("olson/rule",["olson/helpers","olson/timezonetime"],e):(this.WallTime||(this.WallTime={}),this.WallTime.rule=e(this.WallTime.helpers,this.WallTime.TimeZoneTime))}.call(this),function(){var e,t,n,i
e=function(e,t,n){var i,o,r
return i=function(){function i(t,n,i,o,r,s){var l,a,u,h,T,f
this.name=t,this._offset=n,this._rule=i,this.format=o,this._until=r,f=e.Time.ParseGMTOffset(this._offset),a=f[0],u=f[1],h=f[2],T=f[3],this.offset={negative:a,hours:u,mins:h,secs:isNaN(T)?0:T},l=s?e.Time.MakeDateFromTimeStamp(s.range.end.getTime()+1):e.Time.MinDate(),this.range={begin:l,end:this._parseUntilDate(this._until)}}return i.prototype._parseUntilDate=function(t){var n,i,o,r,s,l,a,u,h,T,f,m,p
return m=t.split(" "),f=m[0],l=m[1],n=m[2],T=m[3],p=T?e.Time.ParseGMTOffset(T):[!1,0,0,0],a=p[0],o=p[1],r=p[2],u=p[3],u=isNaN(u)?0:u,f&&""!==f?(f=parseInt(f,10),s=l?e.Months.MonthIndex(l):0,n||(n="1"),n=e.Months.IsDayOfMonthRule(n)?e.Months.DayOfMonthByRule(n,f,s):e.Months.IsLastDayOfMonthRule(n)?e.Months.LastDayOfMonthRule(n,f,s):parseInt(n,10),h=e.Time.StandardTimeToUTC(this.offset,f,s,n,o,r,u),i=e.Time.MakeDateFromTimeStamp(h.getTime()-1)):e.Time.MaxDate()},i.prototype.updateEndForRules=function(n){var i,o,r,s,l
if(this._rule!=="-"&&this._rule!=="")return this._rule.indexOf(":")>=0&&(l=e.Time.ParseTime(this._rule),o=l[0],r=l[1],this.range.end=e.Time.ApplySave(this.range.end,{hours:o,mins:r})),s=new t.RuleSet(n(this._rule),this),i=s.getYearEndDST(this.range.end),this.range.end=e.Time.ApplySave(this.range.end,i)},i.prototype.UTCToWallTime=function(i,o){var r,s,l,a
return this._rule==="-"||this._rule===""?new n(i,this,e.noSave):this._rule.indexOf(":")>=0?(a=e.Time.ParseTime(this._rule),r=a[0],s=a[1],new n(i,this,{hours:r,mins:s})):(l=new t.RuleSet(o(this._rule),this),l.getWallTimeForUTC(i))},i.prototype.WallTimeToUTC=function(n,i){var o,r,s,l
return this._rule==="-"||this._rule===""?e.Time.StandardTimeToUTC(this.offset,n):this._rule.indexOf(":")>=0?(l=e.Time.ParseTime(this._rule),o=l[0],r=l[1],e.Time.WallTimeToUTC(this.offset,{hours:o,mins:r},n)):(s=new t.RuleSet(i(this._rule),this),s.getUTCForWallTime(n,this.offset))},i.prototype.IsAmbiguous=function(n,i){var o,r,s,l,a,u,h,T,f
if(this._rule==="-"||this._rule==="")return!1
if(this._rule.indexOf(":")>=0){if(u=e.Time.StandardTimeToUTC(this.offset,n),h=e.Time.ParseTime(this._rule),r=h[0],l=h[1],s=function(){var t,n
return t={begin:this.range.begin,end:e.Time.ApplySave(this.range.begin,{hours:r,mins:l})},t.end.getTime()<t.begin.getTime()&&(n=t.begin,t.begin=t.end,t.end=n),t},o=s(this.range.begin),o.begin.getTime()<=(T=u.getTime())&&T<o.end.getTime())return!0
o=s(this.range.end),o.begin.getTime()<=(f=u.getTime())&&f<o.end.getTime()}return a=new t.RuleSet(i(this._rule),this),a.isAmbiguous(n,this.offset)},i}(),o=function(){function t(e,t){var n,i,o,r
if(this.zones=null!=e?e:[],this.getRulesNamed=t,this.name=this.zones.length>0?this.zones[0].name:"",r=this.zones,"undefined"!=typeof r)for(i=0,o=r.length;o>i;i++)n=r[i],n.updateEndForRules}return t.prototype.add=function(e){if(this.zones.length===0&&this.name===""&&(this.name=e.name),this.name!==e.name)throw new Error("Cannot add different named zones to a ZoneSet")
return this.zones.push(e)},t.prototype.findApplicable=function(t,n){var i,o,r,s,l,a,u,h
if(null==n&&(n=!1),s=t.getTime(),i=function(t){return{begin:e.Time.UTCToStandardTime(t.range.begin,t.offset),end:e.Time.UTCToStandardTime(t.range.end,t.offset)}},o=null,h=this.zones,"undefined"!=typeof h)for(a=0,u=h.length;u>a;a++)if(l=h[a],r=n?i(l):l.range,r.begin.getTime()<=s&&s<=r.end.getTime()){o=l
break}return o},t.prototype.getWallTimeForUTC=function(t){var i
return i=this.findApplicable(t),i?i.UTCToWallTime(t,this.getRulesNamed):new n(t,e.noZone,e.noSave)},t.prototype.getUTCForWallTime=function(e){var t
return t=this.findApplicable(e,!0),t?t.WallTimeToUTC(e,this.getRulesNamed):e},t.prototype.isAmbiguous=function(e){var t
return t=this.findApplicable(e,!0),t?t.IsAmbiguous(e,this.getRulesNamed):!1},t}(),r={Zone:i,ZoneSet:o}},"undefined"==typeof window?(n=require("./helpers"),i=require("./rule"),t=require("./timezonetime"),module.exports=e(n,i,t)):"undefined"!=typeof define?define("olson/zone",["olson/helpers","olson/rule","olson/timezonetime"],e):(this.WallTime||(this.WallTime={}),this.WallTime.zone=e(this.WallTime.helpers,this.WallTime.rule,this.WallTime.TimeZoneTime))}.call(this),function(){var e,t,n,i,o,r,s,l,a,u,h={}.hasOwnProperty
if(t=function(e,t,n){var i
return i=function(){function i(){}return i.prototype.init=function(e,t){return null==e&&(e={}),null==t&&(t={}),this.zones={},this.rules={},this.addRulesZones(e,t),this.zoneSet=null,this.timeZoneName=null,this.doneInit=!0},i.prototype.addRulesZones=function(e,i){var o,r,s,l,a,u,T,f,m,p,c,d,g
null==e&&(e={}),null==i&&(i={}),o=null
for(m in i)if(h.call(i,m)){for(p=i[m],l=[],o=null,c=0,d=p.length;d>c;c++)f=p[c],s=new n.Zone(f.name,f._offset,f._rule,f.format,f._until,o),l.push(s),o=s
this.zones[m]=l}g=[]
for(u in e)h.call(e,u)&&(T=e[u],r=function(){var e,n,i
for(i=[],e=0,n=T.length;n>e;e++)a=T[e],i.push(new t.Rule(a.name,a._from,a._to,a.type,a["in"],a.on,a.at,a._save,a.letter))
return i}(),g.push(this.rules[u]=r))
return g},i.prototype.setTimeZone=function(e){var t,i=this
if(!this.doneInit)throw new Error("Must call init with rules and zones before setting time zone")
if(!this.zones[e])throw new Error("Unable to find time zone named "+(e||"<blank>"))
return t=this.zones[e],this.zoneSet=new n.ZoneSet(t,function(e){return i.rules[e]}),this.timeZoneName=e},i.prototype.Date=function(t,n,i,o,r,s,l){return null==n&&(n=0),null==i&&(i=1),null==o&&(o=0),null==r&&(r=0),null==s&&(s=0),null==l&&(l=0),t||(t=(new Date).getUTCFullYear()),e.Time.MakeDateFromParts(t,n,i,o,r,s,l)},i.prototype.UTCToWallTime=function(e,t){if(null==t&&(t=this.timeZoneName),"number"==typeof e&&(e=new Date(e)),t!==this.timeZoneName&&this.setTimeZone(t),!this.zoneSet)throw new Error("Must set the time zone before converting times")
return this.zoneSet.getWallTimeForUTC(e)},i.prototype.WallTimeToUTC=function(t,n,i,o,r,s,l,a){var u
return null==t&&(t=this.timeZoneName),null==i&&(i=0),null==o&&(o=1),null==r&&(r=0),null==s&&(s=0),null==l&&(l=0),null==a&&(a=0),t!==this.timeZoneName&&this.setTimeZone(t),u="number"==typeof n?e.Time.MakeDateFromParts(n,i,o,r,s,l,a):n,this.zoneSet.getUTCForWallTime(u)},i.prototype.IsAmbiguous=function(t,n,i,o,r,s){var l
return null==t&&(t=this.timeZoneName),null==s&&(s=0),t!==this.timeZoneName&&this.setTimeZone(t),l="number"==typeof n?e.Time.MakeDateFromParts(n,i,o,r,s):n,this.zoneSet.isAmbiguous(l)},i}(),new i},"undefined"==typeof window)r=require("./olson/zone"),o=require("./olson/rule"),i=require("./olson/helpers"),module.exports=t(i,o,r)
else if("undefined"!=typeof define)define("walltime",["olson/helpers","olson/rule","olson/zone"],t)
else{this.WallTime||(this.WallTime={}),e=t(this.WallTime.helpers,this.WallTime.rule,this.WallTime.zone),l=this.WallTime
for(n in l)h.call(l,n)&&(s=l[n],e[n]=s)
this.WallTime=e,this.WallTime.autoinit&&((a=this.WallTime.data)!=null?a.rules:void 0)&&((u=this.WallTime.data)!=null?u.zones:void 0)&&this.WallTime.init(this.WallTime.data.rules,this.WallTime.data.zones)}}.call(this);
/**
 * Date.parse with progressive enhancement for ISO 8601 <https://github.com/csnover/js-iso8601>
 * © 2011 Colin Snover <http://zetafleet.com>
 * Released under MIT license.
 */
(function (Date, undefined) {
    var origParse = Date.parse, numericKeys = [ 1, 4, 5, 6, 7, 10, 11 ];
    Date.parse = function (date) {
        var timestamp, struct, minutesOffset = 0;

        // ES5 §15.9.4.2 states that the string should attempt to be parsed as a Date Time String Format string
        // before falling back to any implementation-specific date parsing, so that’s what we do, even if native
        // implementations could be faster
        //              1 YYYY                2 MM       3 DD           4 HH    5 mm       6 ss        7 msec        8 Z 9 ±    10 tzHH    11 tzmm
        if ((struct = /^(\d{4}|[+\-]\d{6})(?:-(\d{2})(?:-(\d{2}))?)?(?:T(\d{2}):(\d{2})(?::(\d{2})(?:\.(\d{3}))?)?(?:(Z)|([+\-])(\d{2})(?::(\d{2}))?)?)?$/.exec(date))) {
            // avoid NaN timestamps caused by “undefined” values being passed to Date.UTC
            for (var i = 0, k; (k = numericKeys[i]); ++i) {
                struct[k] = +struct[k] || 0;
            }

            // allow undefined days and months
            struct[2] = (+struct[2] || 1) - 1;
            struct[3] = +struct[3] || 1;

            if (struct[8] !== 'Z' && struct[9] !== undefined) {
                minutesOffset = struct[10] * 60 + struct[11];

                if (struct[9] === '+') {
                    minutesOffset = 0 - minutesOffset;
                }
            }

            timestamp = Date.UTC(struct[1], struct[2], struct[3], struct[4], struct[5] + minutesOffset, struct[6], struct[7]);
        }
        else {
            timestamp = origParse ? origParse(date) : NaN;
        }

        return timestamp;
    };
}(Date));
/**
 * @name jsTimezoneDetect
 * @version 1.0.5
 * @author Jon Nylander
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 * For usage and examples, visit: http://pellepim.bitbucket.org/jstz/
 * Copyright (c) Jon Nylander
 */
var jstz=function(){"use strict"
var a="s",i=function(a){var i=-a.getTimezoneOffset()
return null!==i?i:0},e=function(a,i,e){var r=new Date
return void 0!==a&&r.setFullYear(a),r.setMonth(i),r.setDate(e),r},r=function(a){return i(e(a,0,2))},n=function(a){return i(e(a,5,2))},t=function(a){var e=a.getMonth()>7,t=e?n(a.getFullYear()):r(a.getFullYear()),s=i(a),c=0>t,A=t-s
return c||e?0!==A:0>A},s=function(){var i=r(),e=n(),t=i-e
return 0>t?i+",1":t>0?e+",1,"+a:i+",0"},c=function(){var a=s()
return new jstz.TimeZone(jstz.olson.timezones[a])},A=function(){return s()},o=function(){return jstz.olson.timezones},u=function(a){var i=new Date(2010,6,15,1,0,0,0),e={"America/Denver":new Date(2011,2,13,3,0,0,0),"America/Mazatlan":new Date(2011,3,3,3,0,0,0),"America/Chicago":new Date(2011,2,13,3,0,0,0),"America/Mexico_City":new Date(2011,3,3,3,0,0,0),"America/Asuncion":new Date(2012,9,7,3,0,0,0),"America/Santiago":new Date(2012,9,3,3,0,0,0),"America/Campo_Grande":new Date(2012,9,21,5,0,0,0),"America/Montevideo":new Date(2011,9,2,3,0,0,0),"America/Sao_Paulo":new Date(2011,9,16,5,0,0,0),"America/Los_Angeles":new Date(2011,2,13,8,0,0,0),"America/Santa_Isabel":new Date(2011,3,5,8,0,0,0),"America/Havana":new Date(2012,2,10,2,0,0,0),"America/New_York":new Date(2012,2,10,7,0,0,0),"Europe/Helsinki":new Date(2013,2,31,5,0,0,0),"Pacific/Auckland":new Date(2011,8,26,7,0,0,0),"America/Halifax":new Date(2011,2,13,6,0,0,0),"America/Goose_Bay":new Date(2011,2,13,2,1,0,0),"America/Miquelon":new Date(2011,2,13,5,0,0,0),"America/Godthab":new Date(2011,2,27,1,0,0,0),"Europe/Moscow":i,"Asia/Amman":new Date(2013,2,29,1,0,0,0),"Asia/Beirut":new Date(2013,2,31,2,0,0,0),"Asia/Damascus":new Date(2013,3,6,2,0,0,0),"Asia/Jerusalem":new Date(2013,2,29,5,0,0,0),"Asia/Yekaterinburg":i,"Asia/Omsk":i,"Asia/Krasnoyarsk":i,"Asia/Irkutsk":i,"Asia/Yakutsk":i,"Asia/Vladivostok":i,"Asia/Baku":new Date(2013,2,31,4,0,0),"Asia/Yerevan":new Date(2013,2,31,3,0,0),"Asia/Kamchatka":i,"Asia/Gaza":new Date(2010,2,27,4,0,0),"Africa/Cairo":new Date(2010,4,1,3,0,0),"Europe/Minsk":i,"Pacific/Apia":new Date(2010,10,1,1,0,0,0),"Pacific/Fiji":new Date(2010,11,1,0,0,0),"Australia/Perth":new Date(2008,10,1,1,0,0,0)}
return e[a]}
return{determine:c,offset:A,timezones:o,date_is_dst:t,dst_start_for:u}}()
jstz.TimeZone=function(a){"use strict"
var i={"America/Denver":["America/Denver","America/Mazatlan"],"America/Chicago":["America/Chicago","America/Mexico_City"],"America/Santiago":["America/Santiago","America/Asuncion","America/Campo_Grande"],"America/Montevideo":["America/Montevideo","America/Sao_Paulo"],"Asia/Beirut":["Asia/Amman","Asia/Jerusalem","Asia/Beirut","Europe/Helsinki","Asia/Damascus"],"Pacific/Auckland":["Pacific/Auckland","Pacific/Fiji"],"America/Los_Angeles":["America/Los_Angeles","America/Santa_Isabel"],"America/New_York":["America/Havana","America/New_York"],"America/Halifax":["America/Goose_Bay","America/Halifax"],"America/Godthab":["America/Miquelon","America/Godthab"],"Asia/Dubai":["Europe/Moscow"],"Asia/Dhaka":["Asia/Yekaterinburg"],"Asia/Jakarta":["Asia/Omsk"],"Asia/Shanghai":["Asia/Krasnoyarsk","Australia/Perth"],"Asia/Tokyo":["Asia/Irkutsk"],"Australia/Brisbane":["Asia/Yakutsk"],"Pacific/Noumea":["Asia/Vladivostok"],"Pacific/Tarawa":["Asia/Kamchatka","Pacific/Fiji"],"Pacific/Tongatapu":["Pacific/Apia"],"Asia/Baghdad":["Europe/Minsk"],"Asia/Baku":["Asia/Yerevan","Asia/Baku"],"Africa/Johannesburg":["Asia/Gaza","Africa/Cairo"]},e=a,r=function(){for(var a=i[e],r=a.length,n=0,t=a[0];r>n;n+=1)if(t=a[n],jstz.date_is_dst(jstz.dst_start_for(t)))return e=t,void 0},n=function(){return typeof i[e]!="undefined"}
return n()&&r(),{name:function(){return e}}},jstz.olson={},jstz.olson.timezones={"-720,0":"Pacific/Majuro","-660,0":"Pacific/Pago_Pago","-600,1":"America/Adak","-600,0":"Pacific/Honolulu","-570,0":"Pacific/Marquesas","-540,0":"Pacific/Gambier","-540,1":"America/Anchorage","-480,1":"America/Los_Angeles","-480,0":"Pacific/Pitcairn","-420,0":"America/Phoenix","-420,1":"America/Denver","-360,0":"America/Guatemala","-360,1":"America/Chicago","-360,1,s":"Pacific/Easter","-300,0":"America/Bogota","-300,1":"America/New_York","-270,0":"America/Caracas","-240,1":"America/Halifax","-240,0":"America/Santo_Domingo","-240,1,s":"America/Santiago","-210,1":"America/St_Johns","-180,1":"America/Godthab","-180,0":"America/Argentina/Buenos_Aires","-180,1,s":"America/Montevideo","-120,0":"America/Noronha","-60,1":"Atlantic/Azores","-60,0":"Atlantic/Cape_Verde","0,0":"UTC","0,1":"Europe/London","60,1":"Europe/Berlin","60,0":"Africa/Lagos","60,1,s":"Africa/Windhoek","120,1":"Asia/Beirut","120,0":"Africa/Johannesburg","180,0":"Asia/Baghdad","180,1":"Europe/Moscow","210,1":"Asia/Tehran","240,0":"Asia/Dubai","240,1":"Asia/Baku","270,0":"Asia/Kabul","300,1":"Asia/Yekaterinburg","300,0":"Asia/Karachi","330,0":"Asia/Kolkata","345,0":"Asia/Kathmandu","360,0":"Asia/Dhaka","360,1":"Asia/Omsk","390,0":"Asia/Rangoon","420,1":"Asia/Krasnoyarsk","420,0":"Asia/Jakarta","480,0":"Asia/Shanghai","480,1":"Asia/Irkutsk","525,0":"Australia/Perth","540,1":"Asia/Yakutsk","540,0":"Asia/Tokyo","570,0":"Australia/Darwin","570,1,s":"Australia/Adelaide","600,0":"Australia/Brisbane","600,1":"Asia/Vladivostok","600,1,s":"Australia/Sydney","630,1,s":"Australia/Lord_Howe","660,1":"Asia/Kamchatka","660,0":"Pacific/Noumea","690,0":"Pacific/Norfolk","720,1,s":"Pacific/Auckland","720,0":"Pacific/Tarawa","765,1,s":"Pacific/Chatham","780,0":"Pacific/Tongatapu","780,1,s":"Pacific/Apia","840,0":"Pacific/Kiritimati"};
  /*
   * Provide Date.toISOString() for older (i.e. MSIE8) browsers
   */
  if (!Date.prototype.toISOString) {
    Date.prototype.toISOString = function (key) {
      function pad(n) {return parseInt(n) < 10 ? '0' + n : n;}
      return this.getUTCFullYear()   + '-' +
        pad(this.getUTCMonth() + 1) + '-' +
        pad(this.getUTCDate())      + 'T' +
        pad(this.getUTCHours())     + ':' +
        pad(this.getUTCMinutes())   + ':' +
        pad(this.getUTCSeconds())   + 'Z';
    };
  }


  /**
   * With all that frameworky stuff out of the way, let's move on to the real code
   */
  // Picker object
  var DateTimePicker = function(element, options) {
    this.id = dpgId++;
    this.init(element, options);
  };

  DateTimePicker.prototype = {
    jstz: jstz,
    _date: new Date(),

    constructor: DateTimePicker,

    init: function(element, options) {
      this.options = options;
      this.$element = $(element);
      this.language = 'en';
      //jQuery 1.9 ditched $.browser, so I'm checking for leadingWhitespace support
      //(something IE6-8 don't do)
      if ($.support.leadingWhitespace === false) {
	this.notifyError('This web browser is too old to use the datetimepicker plugin; please update to something more recent than MSIE8!');
	this.disable();
	return false;
      }
      else {

	this.isInput = this.$element.is('input');
	this.component = false;
	if (this.$element.is('.input-append') || this.$element.is('.input-prepend')) {
          this.component = this.$element.find('.add-on');
	}

	this.format = 'iso8601';
	this.timeIcon = 'icon-time';
	this.dateIcon = 'icon-calendar';

	this._timezone = this.jstz.determine().name();
	this._oldtz = this._timezone;

	var templateopts = {
	  timeIcon: this.timeIcon,
	  collapse: options.collapse,
	  currTZ: this._timezone};

	this.widget = $(getTemplate(templateopts)).appendTo('body');
	this.startViewMode = this.viewMode = this.minViewMode = 0;

	this.weekStart = options.weekStart||this.$element.data('date-weekstart')||0;
	this.weekEnd = this.weekStart === 0 ? 6 : this.weekStart - 1;
	this.fillDow();
	this.fillMonths();
	this.fillHours();
	this.fillMinutes();
	this.fillSeconds();

	var zones = [];

	$.each(WallTime.data.zones,
	       function(name, zone) {
		 zones.push({name: name, offset:zone[0]['_offset']});
	       });

	zones = zones.sort(function(a,b) {
	  var ao = parseFloat(a.offset.replace(':', '.'), 10);
	  var bo = parseFloat(b.offset.replace(':', '.'), 10);
	  return ao - bo;
	});
	this.fillTZ(zones);

	this.setup();
	this.set();

	this.showMode();
	this._attachDatePickerEvents();
      }
    },

    show: function(e) {
      this.widget.show();
      this.height = this.component ? this.component.outerHeight() : this.$element.outerHeight();
      this.place();
      this.$element.trigger({
        type: 'show',
        date: this._date
      });
      this._attachDatePickerGlobalEvents();
      if (e) {
        e.stopPropagation();
        e.preventDefault();
      }
    },

    disable: function(){
      this.$element.find('input').prop('disabled',true);
      this._detachDatePickerEvents();
    },
    enable: function(){
      this.$element.find('input').prop('disabled',false);
      this._attachDatePickerEvents();
    },

    hide: function() {
      // Ignore event if in the middle of a picker transition
      var collapse = this.widget.find('.collapse')
      for (var i = 0; i < collapse.length; i++) {
        var collapseData = collapse.eq(i).data('collapse');
        if (collapseData && collapseData.transitioning)
          return;
      }
      this.widget.hide();
      this.viewMode = this.startViewMode;
      this.showMode();
      this.$element.trigger({
        type: 'hide',
        date: this._date
      });
      if (typeof(this.$element.find('input').val()) !== 'undefined') {
	this.set();
      }
      this._detachDatePickerGlobalEvents();
    },

    makeoffset: function(offset) {
      //offset is in minutes; we want [+-]HHMM
      offset = parseFloat(offset/60).toFixed(2);

      var hours = offset.split('.')[0].toString();
      var minutes = (parseInt((offset.split('.')[1] / 100) * 60)).toString();

      if (hours.length === 1 || hours.substr(0,1) === '-' && hours.length === 2) {
	hours = "0" + hours;
      }

      if (hours.substr(0,1) !== '-') {
	hours = "+" + hours;
      }

      if (minutes.length === 1) {
	minutes = "0" + minutes;
      }

      return hours + minutes;
    },

    // this sets the text box value with the value of this._date
    set: function() {
      var formatted = !this._unset ? this._date.toISOString().replace(/\.?\d*Z$/, 'Z') : '';
      if (!this.isInput) {
        if (this.component){
          var input = this.$element.find('input');
          input.val(formatted);
          input.trigger('input');
        }
      } else {
        this.$element.val(formatted);
      }
    },

    // this sets the value of this._date to the supplied newDate,
    // and resets the view (this.fillDate(), this.fillTime()) after
    // calling this.set()
    propagate: function(newDate) {
      if (!newDate) {
        this._unset = true;
      } else {
        this._unset = false;
      }
      if (typeof newDate === 'string') {
        this._date = this.parseDate(newDate);
      } else if(newDate) {
        this._date = new Date(newDate);
      }
      this.set();
      this.fillDate();
      this.fillTime();
    },

    getDate: function() {
      if (this._unset) return null;
      return new Date(this._date.valueOf());
    },

    setDate: function(date) {
      if (!date) this.propagate(null);
      else this.propagate(date.valueOf());
    },

    place: function(){
      var position = 'absolute';
      var offset = this.component ? this.component.offset() : this.$element.offset();
      offset.top = offset.top + this.height;

      if (this._isInFixed()) {
        var $window = $(window);
        position = 'fixed';
        offset.top -= $window.scrollTop();
        offset.left -= $window.scrollLeft();
      }

      this.widget.css({
        position: position,
        top: offset.top,
        left: offset.left
      });
    },

    // this exposes the current datetime via a trigger
    notifyChange: function() {
      this.validate();
      this.$element.trigger('change.datepicker.ands',
			    {utc: this._walltime.utc.toISOString(),
			     tz: this._timezone});
      this.$element.trigger('input');
    },

    notifyError: function(msg) {
      this.$element.trigger('error.datepicker.ands',
			    {msg: msg});
    },

    validate: function() {
      var dateStr;
      var valid = true;
      var nd = new Date();
      if (this.isInput) {
        dateStr = this.$element.val();
      } else {
        dateStr = this.$element.find('input').val();
      }
      try {
	nd = this.parseDate(dateStr);
      }
      catch (ex) {
	valid = false;
      }
      valid = valid && (nd.toString() !== 'Invalid Date');
      this.$element.trigger('valid.datepicker.ands',
			    valid);
    },

    // this retrieves the value of the text box
    // and resets this._date accordingly.
    // the view is the reset (via this.fillDate(),
    // this.fillTime())
    setup: function(){
      var dateStr;
      var nd = null;
      if (this.isInput) {
        dateStr = this.$element.val();
      } else {
        dateStr = this.$element.find('input').val();
      }
      if (dateStr) {
        this._date = this.parseDate(dateStr);
	try {
	  this.set();
	}
	catch (ex) {
	  this.notifyError('Supplied date "' + dateStr + '" is invalid; reverting to "today-and-now"');
	  nd = new Date();
	  this._date = this.parseDate(nd.toISOString());
	}
      }
      else {
	nd = new Date();
	this._date = this.parseDate(nd.toISOString());
      }
      this.fillDate();
      this.fillTime();
    },

    parseDate: function(str) {
      this._walltime = WallTime.UTCToWallTime(Date.UTC(1970, 0, 1, 0, 0, 0, Date.parse(str)),
					      this._timezone);
      return this._walltime.utc;
    },

    fillDow: function() {
      var dowCnt = this.weekStart;
      var html = '<tr>';
      while (dowCnt < this.weekStart + 7) {
        html += '<th class="dow">' + dates[this.language].daysMin[(dowCnt++) % 7] + '</th>';
      }
      html += '</tr>';
      this.widget.find('.datepicker-days thead').append(html);
    },

    fillMonths: function() {
      var html = '';
      var i = 0
      while (i < 12) {
        html += '<span class="month">' + dates[this.language].monthsShort[i++] + '</span>';
      }
      this.widget.find('.datepicker-months td').append(html);
    },

    fillDate: function(theDate) {
      if (typeof(theDate) === 'undefined') {
	theDate = this._walltime.wallTime;
      }

      var year = theDate.getUTCFullYear();
      var month = theDate.getUTCMonth();
      var currentDate = UTCDate(
        theDate.getUTCFullYear(),
        theDate.getUTCMonth(),
        theDate.getUTCDate(),
        0, 0, 0, 0
      );

      this.widget.find('.datepicker-days').find('.disabled').removeClass('disabled');
      this.widget.find('.datepicker-months').find('.disabled').removeClass('disabled');
      this.widget.find('.datepicker-years').find('.disabled').removeClass('disabled');

      this.widget.find('.datepicker-days th:eq(1)').text(
        dates[this.language].months[month] + ' ' + year);

      var prevMonth = UTCDate(year, month-1, 28, 0, 0, 0, 0);
      var day = DPGlobal.getDaysInMonth(
        prevMonth.getUTCFullYear(), prevMonth.getUTCMonth());
      prevMonth.setUTCDate(day);
      prevMonth.setUTCDate(day - (prevMonth.getUTCDay() - this.weekStart + 7) % 7);

      var nextMonth = new Date(prevMonth.valueOf());
      nextMonth.setUTCDate(nextMonth.getDate() + 42);
      nextMonth = nextMonth.valueOf();
      var html = [];
      var clsName;
      while (prevMonth.valueOf() < nextMonth) {
        if (prevMonth.getUTCDay() === this.weekStart) {
          html.push('<tr>');
        }
        clsName = '';
        if (prevMonth.getUTCFullYear() < year ||
            (prevMonth.getUTCFullYear() == year &&
             prevMonth.getUTCMonth() < month)) {
          clsName += ' old';
        } else if (prevMonth.getUTCFullYear() > year ||
                   (prevMonth.getUTCFullYear() == year &&
                    prevMonth.getUTCMonth() > month)) {
          clsName += ' new';
        }
        if (prevMonth.valueOf() === currentDate.valueOf()) {
          clsName += ' active';
        }
        if ((prevMonth.valueOf() + 86400000) <= this.startDate) {
          clsName += ' disabled';
        }
        if (prevMonth.valueOf() > this.endDate) {
          clsName += ' disabled';
        }
        html.push('<td class="day' + clsName + '">' + prevMonth.getUTCDate() + '</td>');
        if (prevMonth.getDay() === this.weekEnd) {
          html.push('</tr>');
        }
        prevMonth.setUTCDate(prevMonth.getUTCDate() + 1);
      }
      this.widget.find('.datepicker-days tbody').empty().append(html.join(''));
      var currentYear = theDate.getUTCFullYear();

      var months = this.widget.find('.datepicker-months').find(
        'th:eq(1)').text(year).end().find('span').removeClass('active');
      if (currentYear === year) {
        months.eq(theDate.getUTCMonth()).addClass('active');
      }

      html = '';
      year = parseInt(year/10, 10) * 10;
      var yearCont = this.widget.find('.datepicker-years').find(
        'th:eq(1)').text(year + '-' + (year + 9)).end().find('td');
      this.widget.find('.datepicker-years').find('th').removeClass('disabled');
      year -= 1;
      for (var i = -1; i < 11; i++) {
        html += '<span class="year' + (i === -1 || i === 10 ? ' old' : '') + (currentYear === year ? ' active' : '') +  '">' + year + '</span>';
        year += 1;
      }
      yearCont.html(html);
    },

    fillHours: function() {
      var table = this.widget.find(
        '.timepicker .timepicker-hours table');
      table.parent().hide();
      var html = '';
      var current = 0;
      for (var i = 0; i < 6; i += 1) {
        html += '<tr>';
        for (var j = 0; j < 4; j += 1) {
          var c = current.toString();
          html += '<td class="hour">' + padLeft(c, 2, '0') + '</td>';
          current++;
        }
        html += '</tr>'
      }
      table.html(html);
    },

    fillMinutes: function() {
      var table = this.widget.find(
        '.timepicker .timepicker-minutes table');
      table.parent().hide();
      var html = '';
      var current = 0;
      for (var i = 0; i < 5; i++) {
        html += '<tr>';
        for (var j = 0; j < 4; j += 1) {
          var c = current.toString();
          html += '<td class="minute">' + padLeft(c, 2, '0') + '</td>';
          current += 3;
        }
        html += '</tr>';
      }
      table.html(html);
    },

    fillSeconds: function() {
      var table = this.widget.find(
        '.timepicker .timepicker-seconds table');
      table.parent().hide();
      var html = '';
      var current = 0;
      for (var i = 0; i < 5; i++) {
        html += '<tr>';
        for (var j = 0; j < 4; j += 1) {
          var c = current.toString();
          html += '<td class="second">' + padLeft(c, 2, '0') + '</td>';
          current += 3;
        }
        html += '</tr>';
      }
      table.html(html);
    },

    fillTZ: function(timezones) {
      var list = this.widget.find(
	'.timepicker .timepicker-tz ul');
      list.parent().hide();
      list.append('<li class="alert-warning alert">' +
		    '<small>Offsets are for standard time; ' +
		    'daylight saving time offsets are automatically ' +
		    'calculated as required.</small>' +
		  '</li>');
      list.append('<li><button data-tz-toggle="true" class="btn-small btn-block btn-success">Show all timezones</button></li>');
      $.each(timezones, function(idx, tz) {
	var offset = tz.offset;
	if (offset.substr(0,1) !== '-') {
	  offset = "+" + offset;
	}
	var name = tz.name;
	var intl = name.indexOf('Australia/') !== 0 ? 'yes' : 'no';

	var li = $('<li data-intl="' + intl + '" />');
	var button = $('<button class="btn btn-block" ' +
		       'data-tz="' + name + '" />');

	if (name !== "UTC") {
	  button.html(name + ' <small>(' + offset + ')</small>');
	}
	else {
	  button.html(name);
	}

	li.append(button);
	list.append(li);
      });
    },

    fillTime: function(theTime) {
      if (typeof(theTime) === 'undefined') {
	theTime = this._walltime.wallTime;
      }

      if (!theTime)
        return;

      var timeComponents = this.widget.find('.timepicker span[data-time-component]');
      var table = timeComponents.closest('table');
      var hour = theTime.getUTCHours();

      hour = padLeft(hour.toString(), 2, '0');
      var minute = padLeft(theTime.getUTCMinutes().toString(), 2, '0');
      var second = padLeft(theTime.getUTCSeconds().toString(), 2, '0');
      timeComponents.filter('[data-time-component=hours]').text(hour);
      timeComponents.filter('[data-time-component=minutes]').text(minute);
      timeComponents.filter('[data-time-component=seconds]').text(second);
    },

    click: function(e) {
      e.stopPropagation();
      e.preventDefault();
      this._unset = false;
      var target = $(e.target).closest('span, td, th');
      var newdate = this._walltime.wallTime;
      var doNotify = false;


      if (target.length === 1) {
        if (! target.is('.disabled')) {
          switch(target[0].nodeName.toLowerCase()) {
          case 'th':
            switch(target[0].className) {
            case 'switch':
              this.showMode(1);
              break;
            case 'prev':
            case 'next':
              var navFnc = DPGlobal.modes[this.viewMode].navFnc;
              var step = DPGlobal.modes[this.viewMode].navStep;
              if (target[0].className === 'prev') step = step * -1;
              newdate['set' + navFnc](newdate['get' + navFnc]() + step);
              break;
            }
	    doNotify = true;
            break;
          case 'span':
            if (target.is('.month')) {
              var month = target.parent().find('span').index(target);
              newdate.setUTCMonth(month);
            } else {
              var year = parseInt(target.text(), 10) || 0;
              newdate.setUTCFullYear(year);
            }
	    doNotify = true;
            this.showMode(-1);
            break;
          case 'td':
            if (target.is('.day')) {
	      doNotify = true;
              var day = parseInt(target.text(), 10) || 1;
              var month = newdate.getUTCMonth();
              var year = newdate.getUTCFullYear();
              if (target.is('.old')) {
                if (month === 0) {
                  month = 11;
                  year -= 1;
                } else {
                  month -= 1;
                }
              } else if (target.is('.new')) {
                if (month == 11) {
                  month = 0;
                  year += 1;
                } else {
                  month += 1;
                }
              }
	      newdate.setUTCDate(day);
	      newdate.setUTCMonth(month);
	      newdate.setUTCFullYear(year);
            }
            break;
          }

	  this._walltime = WallTime.UTCToWallTime(WallTime.WallTimeToUTC(this._timezone,
									 newdate.getUTCFullYear(),
									 newdate.getUTCMonth(),
									 newdate.getUTCDate(),
									 newdate.getUTCHours(),
									 newdate.getUTCMinutes(),
									 newdate.getUTCSeconds()),
						  this._timezone);

          this._date = this._walltime.utc;
          this.fillDate();
          this.fillTime();
          this.set();
	  if (doNotify) {
            this.notifyChange();
	  }

        }
      }
    },

    actions: {
      incrementHours: function(e) {
	this._date.setUTCHours(this._date.getUTCHours() + 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      incrementMinutes: function(e) {
	this._date.setUTCMinutes(this._date.getUTCMinutes() + 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      incrementSeconds: function(e) {
	this._date.setUTCSeconds(this._date.getUTCSeconds() + 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      decrementHours: function(e) {
	this._date.setUTCHours(this._date.getUTCHours() - 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      decrementMinutes: function(e) {
	this._date.setUTCMinutes(this._date.getUTCMinutes() - 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      decrementSeconds: function(e) {
	this._date.setUTCSeconds(this._date.getUTCSeconds() - 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      showPicker: function() {
        this.widget.find('.timepicker > div:not(.timepicker-picker)').hide();
        this.widget.find('.timepicker .timepicker-picker').show();
      },

      showHours: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
        this.widget.find('.timepicker .timepicker-hours').show();
      },

      showMinutes: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
        this.widget.find('.timepicker .timepicker-minutes').show();
      },

      showSeconds: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
        this.widget.find('.timepicker .timepicker-seconds').show();
      },

      showTZ: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
	this.widget.find('.timepicker .timepicker-tz button').removeClass('btn-primary');
	this.widget.find('.timepicker .timepicker-tz button[data-tz="' + this._timezone +'"]').addClass('btn-primary');
        this.widget.find('.timepicker .timepicker-tz').show();
	this.widget.find('.timepicker .timepicker-tz ul.unstyled li[data-intl=yes]').hide();
	this.widget.find('.timepicker .timepicker-tz ul.unstyled button.btn-primary').parent().show();
	this.widget.find('.timepicker .timepicker-tz ul.unstyled button.btn-success').parent().show();
      },

      selectTZ: function(e) {
	var tgt = $(e.target);
	if (tgt.is('button') && typeof(tgt.data('tz')) !== 'undefined') {
	  var tz = tgt.data('tz');
	  var label = $('span.timepicker-tz');
	  label.html('<i class="icon-globe"> </i> ' + tz);
	  this._walltime = WallTime.UTCToWallTime(this._date, tz);
	  this._oldtz = $.extend({}, true, this._timezone);
	  this._timezone = tz;
          this.actions.showPicker.call(this);
	}
	else if (tgt.is('button') && typeof(tgt.data('tz-toggle')) !== 'undefined') {
	  this.widget.find('.timepicker .timepicker-tz ul.unstyled li[data-intl=yes]').show();
	  tgt.parent().hide();
	}
      },

      selectHour: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
	var newdate = this._walltime.wallTime;
	newdate.setUTCHours(value);
	this._walltime = WallTime.UTCToWallTime(WallTime.WallTimeToUTC(this._timezone,
								       newdate.getUTCFullYear(),
								       newdate.getUTCMonth(),
								       newdate.getUTCDate(),
								       newdate.getUTCHours(),
								       newdate.getUTCMinutes(),
								       newdate.getUTCSeconds()),
						this._timezone);
        this.actions.showPicker.call(this);
      },

      selectMinute: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
	var newdate = this._walltime.wallTime;
	newdate.setUTCMinutes(value);
	this._walltime = WallTime.UTCToWallTime(WallTime.WallTimeToUTC(this._timezone,
								       newdate.getUTCFullYear(),
								       newdate.getUTCMonth(),
								       newdate.getUTCDate(),
								       newdate.getUTCHours(),
								       newdate.getUTCMinutes(),
								       newdate.getUTCSeconds()),
						this._timezone);
        this.actions.showPicker.call(this);
      },

      selectSecond: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
	var newdate = this._walltime.wallTime;
	newdate.setUTCSeconds(value);
	this._walltime = WallTime.UTCToWallTime(WallTime.WallTimeToUTC(this._timezone,
								       newdate.getUTCFullYear(),
								       newdate.getUTCMonth(),
								       newdate.getUTCDate(),
								       newdate.getUTCHours(),
								       newdate.getUTCMinutes(),
								       newdate.getUTCSeconds()),
						this._timezone);
        this.actions.showPicker.call(this);
      }

    },

    doAction: function(e) {
      e.stopPropagation();
      e.preventDefault();
      var target = $(e.target);
      if (target.parent().is('button')) {
	target.parent().trigger('click');
      }
      else if (target.parent().parent().is('button')) {
	target.parent().parent().trigger('click');
      }
      else if (target.is('table, tr, tbody, thead, th')) {
	return;
      }

      var action = $(e.currentTarget).data('action');
      var rv = this.actions[action].apply(this, arguments);
      this._date = this._walltime.utc;
      this.set();
      this.fillDate();
      this.fillTime();
      this.notifyChange();
      return rv;
    },

    stopEvent: function(e) {
      e.stopPropagation();
      e.preventDefault();
    },

    showMode: function(dir) {
      if (dir) {
        this.viewMode = Math.max(this.minViewMode, Math.min(
          2, this.viewMode + dir));
      }
      this.widget.find('.datepicker > div').hide().filter(
        '.datepicker-'+DPGlobal.modes[this.viewMode].clsName).show();
    },

    destroy: function() {
      this._detachDatePickerEvents();
      this._detachDatePickerGlobalEvents();
      this.widget.remove();
      this.$element.removeData('ands_datetimepicker');
      this.component.removeData('ands_datetimepicker');
    },

    _attachDatePickerEvents: function() {
      var self = this;
      // this handles date picker clicks
      this.widget.on('click', '.datepicker *', $.proxy(this.click, this));
      // this handles time picker clicks
      this.widget.on('click', '[data-action]', $.proxy(this.doAction, this));
      this.widget.on('mousedown', $.proxy(this.stopEvent, this));
      this.widget.on('click.togglePicker', '.accordion-toggle', function(e) {
        e.stopPropagation();
        var $this = $(this);
        var $parent = $this.closest('ul');
        var expanded = $parent.find('.collapse.in');
        var closed = $parent.find('.collapse:not(.in)');

        if (expanded && expanded.length) {
          var collapseData = expanded.data('collapse');
          if (collapseData && collapseData.transitioning) return;
          expanded.collapse('hide');
          closed.collapse('show')
          $this.find('i').toggleClass(self.timeIcon + ' ' + self.dateIcon);
        }
      });

      if (this.isInput) {
        this.$element.on({
          'focus': $.proxy(this.show, this),
	  'blur': $.proxy(this.hide, this)
        });
      } else {
        if (this.component){
          this.component.on('click', $.proxy(this.show, this));
	  this.$element.on('click', $.proxy(this.show, this));
        } else {
          this.$element.on('click', $.proxy(this.show, this));
        }
      }
    },

    _attachDatePickerGlobalEvents: function() {
      $(window).on(
        'resize.ands_datetimepicker' + this.id, $.proxy(this.place, this));
      if (!this.isInput) {
        $(document).on(
          'mousedown.ands_datetimepicker' + this.id, $.proxy(this.hide, this));
      }
    },

    _detachDatePickerEvents: function() {
      if (typeof(this.widget) !== 'undefined') {
	this.widget.off('click', '.datepicker *', this.click);
	this.widget.off('click', '[data-action]');
	this.widget.off('mousedown', this.stopEvent);
	this.widget.off('click.togglePicker');
      }

      if (this.isInput) {
        this.$element.off({
          'focus': this.show
        });
        if (this.options.maskInput) {
          this.$element.off({
            'keydown': this.keydown,
            'keypress': this.keypress
          });
        }
      } else {
        if (this.options.maskInput) {
          this.$element.off({
            'keydown': this.keydown,
            'keypress': this.keypress
          }, 'input');
        }
        if (this.component){
          this.component.off('click', this.show);
        } else {
          this.$element.off('click', this.show);
        }
      }
    },

    _detachDatePickerGlobalEvents: function () {
      $(window).off('resize.ands_datetimepicker' + this.id);
      if (!this.isInput) {
        $(document).off('mousedown.ands_datetimepicker' + this.id);
      }
    },

    _isInFixed: function() {
      if (this.$element) {
        var parents = this.$element.parents();
        var inFixed = false;
        for (var i=0; i<parents.length; i++) {
          if ($(parents[i]).css('position') == 'fixed') {
            inFixed = true;
            break;
          }
        };
        return inFixed;
      } else {
        return false;
      }
    }
  };

  $.fn.ands_datetimepicker = function ( option, val ) {
    return this.each(function () {
      var $this = $(this),
      data = $this.data('ands_datetimepicker'),
      options = typeof option === 'object' && option;
      if (!data) {
        $this.data('ands_datetimepicker',
		   (data = new DateTimePicker(
		     this,
		     $.extend({},
			      $.fn.ands_datetimepicker.defaults,
			      options))));
      }
      if (typeof option === 'string') data[option](val);
    });
  };

  $.fn.ands_datetimepicker.defaults = {
    collapse: true
  };
  $.fn.ands_datetimepicker.Constructor = DateTimePicker;

  var dpgId = 0;
  var dates = $.fn.ands_datetimepicker.dates = {
    en: {
      days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday",
             "Friday", "Saturday", "Sunday"],
      daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
      daysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
      months: ["January", "February", "March", "April", "May", "June",
               "July", "August", "September", "October", "November", "December"],
      monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul",
		    "Aug", "Sep", "Oct", "Nov", "Dec"]
    }
  };


  function escapeRegExp(str) {
    // http://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
  }

  function padLeft(s, l, c) {
    if (l < s.length) return s;
    else return Array(l - s.length + 1).join(c || ' ') + s;
  }

  function getTemplate(opts) {
    return (
        '<div class="bootstrap-datetimepicker-widget dropdown-menu">' +
          '<ul>' +
            '<li' + (opts.collapse ? ' class="collapse in"' : '') + '>' +
              '<div class="datepicker">' +
                DPGlobal.template +
              '</div>' +
            '</li>' +
            '<li class="picker-switch accordion-toggle"><button class="btn btn-block"><i class="icon-time"></i></button></li>' +
            '<li' + (opts.collapse ? ' class="collapse"' : '') + '>' +
              '<div class="timepicker">' +
                TPGlobal.getTemplate({currTz: opts.currTZ}) +
              '</div>' +
            '</li>' +
          '</ul>' +
        '</div>'
      );
  }

  function UTCDate() {
    return new Date(Date.UTC.apply(Date, arguments));
  }

  var DPGlobal = {
    modes: [
      {
      clsName: 'days',
      navFnc: 'UTCMonth',
      navStep: 1
    },
    {
      clsName: 'months',
      navFnc: 'UTCFullYear',
      navStep: 1
    },
    {
      clsName: 'years',
      navFnc: 'UTCFullYear',
      navStep: 10
    }],
    isLeapYear: function (year) {
      return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0))
    },
    getDaysInMonth: function (year, month) {
      return [31, (DPGlobal.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month]
    },
    headTemplate:
      '<thead>' +
        '<tr>' +
          '<th class="prev">&lsaquo;</th>' +
          '<th colspan="5" class="switch"></th>' +
          '<th class="next">&rsaquo;</th>' +
        '</tr>' +
      '</thead>',
    contTemplate: '<tbody><tr><td colspan="7"></td></tr></tbody>'
  };
  DPGlobal.template =
    '<div class="datepicker-days">' +
      '<table class="table-condensed">' +
        DPGlobal.headTemplate +
        '<tbody></tbody>' +
      '</table>' +
    '</div>' +
    '<div class="datepicker-months">' +
      '<table class="table-condensed">' +
        DPGlobal.headTemplate +
        DPGlobal.contTemplate+
      '</table>'+
    '</div>'+
    '<div class="datepicker-years">'+
      '<table class="table-condensed">'+
        DPGlobal.headTemplate+
        DPGlobal.contTemplate+
      '</table>'+
    '</div>';
  var TPGlobal = {
    hourTemplate: '<span data-action="showHours" data-time-component="hours" class="timepicker-hour"></span>',
    minuteTemplate: '<span data-action="showMinutes" data-time-component="minutes" class="timepicker-minute"></span>',
    secondTemplate: '<span data-action="showSeconds" data-time-component="seconds" class="timepicker-second"></span>',
    tzTemplate: function(opts) {
      var wrapper = $('<div />');
      var container = $('<span data-action="showTZ" class="timepicker-tz"/>');
      container.html('<i class="icon-globe"> </i> ' + opts.current.replace('_', ' '));
      wrapper.append(container);
      return wrapper.html();
    }
  };
  TPGlobal.getTemplate = function(opts) {
    return (
    '<div class="timepicker-picker">' +
      '<table class="table-condensed">' +
        '<tr>' +
          '<td><button href="#" class="btn" data-action="incrementHours"><i class="icon-chevron-up"></i></button></td>' +
          '<td class="separator"></td>' +
          '<td><button href="#" class="btn" data-action="incrementMinutes"><i class="icon-chevron-up"></i></button></td>' +
	  '<td class="separator"></td>' +
          '<td><button href="#" class="btn" data-action="incrementSeconds"><i class="icon-chevron-up"></i></button></td>' +
        '</tr>' +
        '<tr>' +
          '<td>' + TPGlobal.hourTemplate + '</td> ' +
          '<td class="separator">:</td>' +
          '<td>' + TPGlobal.minuteTemplate + '</td> ' +
          '<td class="separator">:</td>' +
          '<td>' + TPGlobal.secondTemplate + '</td>' +
        '</tr>' +
        '<tr>' +
          '<td><button href="#" class="btn" data-action="decrementHours"><i class="icon-chevron-down"></i></button></td>' +
          '<td class="separator"></td>' +
          '<td><button href="#" class="btn" data-action="decrementMinutes"><i class="icon-chevron-down"></i></button></td>' +
	  '<td class="separator"></td>' +
          '<td><button href="#" class="btn" data-action="decrementSeconds"><i class="icon-chevron-down"></i></button></td>' +
        '</tr>' +
	 '<tr>' +
	   '<td colspan="5">' +
	     TPGlobal.tzTemplate({current:opts.currTz}) +
	   '</td>' +
	 '</tr>' +
      '</table>' +
    '</div>' +
    '<div class="timepicker-hours" data-action="selectHour">' +
      '<table class="table-condensed">' +
      '</table>'+
    '</div>'+
    '<div class="timepicker-minutes" data-action="selectMinute">' +
      '<table class="table-condensed">' +
      '</table>'+
    '</div>'+
    '<div class="timepicker-seconds" data-action="selectSecond">' +
      '<table class="table-condensed">' +
      '</table>'+
    '</div>'+
    '<div class="timepicker-tz" data-action="selectTZ">' +
      '<ul class="unstyled">' +
      '</ul>' +
    '</div>'
    );
  }
})( jQuery )
