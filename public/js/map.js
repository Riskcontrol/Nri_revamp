// ------- CONFIGURE YOUR RISK DATA HERE -------
// Map each state's NAME_1 (or 'state' property in your GeoJSON) to a risk category.
// const riskByState = {
//     Lagos: "medium",
//     Ogun: "high",
//     Oyo: "critical",
//     Osun: "critical",
//     Ondo: "critical",
//     Ekiti: "critical",
//     Kano: "low",
//     Kaduna: "low",
//     Katsina: "low",
//     Kebbi: "low",
//     Sokoto: "low",
//     Zamfara: "medium",
//     Jigawa: "low",
//     Bauchi: "low",
//     Gombe: "low",
//     Yobe: "low",
//     Borno: "low",
//     Plateau: "low",
//     Niger: "low",
//     FCT: "low",
//     Nasarawa: "low",
//     Benue: "high",
//     Kogi: "medium",
//     Kwara: "medium",
//     Taraba: "low",
//     Adamawa: "low",
//     "Cross River": "low",
//     "Akwa Ibom": "low",
//     Rivers: "high",
//     Bayelsa: "critical",
//     Delta: "high",
//     Edo: "high",
//     Anambra: "high",
//     Enugu: "high",
//     Ebonyi: "high",
//     Imo: "high",
//     Abia: "high",
// };

// const colorByCategory = {
//     critical: "#C1121F",
//     high: "#F97316",
//     medium: "#FBBF24",
//     low: "#16A34A",
// };

// // Load Nigeria ADM1 GeoJSON (absolute first, with relative fallback)
// const geoUrlAbs = `${location.origin}/data/nigeria-state.geojson`;
// const geoUrlRel = "./data/nigeria-state.geojson";
// console.log("Attempting to load GeoJSON from", geoUrlAbs);
// fetch(geoUrlAbs)
//     .then((r) => r.json())
//     .then((geojson) => {
//         console.log("GeoJSON loaded", geojson);
//         // chartjs-chart-geo expects features; use property name present in your file (often NAME_1 or state)
//         const features = (geojson.features || []).map((f) => {
//             // Try common property keys (prefer 'name' from provided GeoJSON)
//             const n =
//                 f.properties.name ||
//                 f.properties.NAME_1 ||
//                 f.properties.state ||
//                 f.properties.admin1Name ||
//                 "";
//             const cat = riskByState[n] || "low";
//             return {
//                 feature: f,
//                 value: cat,
//                 name: n,
//             };
//         });

//         if (!features.length) {
//             console.error("No features found in GeoJSON.");
//             alert(
//                 "GeoJSON loaded but contains no features. Check file format."
//             );
//             return;
//         }

//         const ctx = document.getElementById("ngMap").getContext("2d");
//         // Build a FeatureCollection of Nigeria states for outline
//         const nigeriaFC = {
//             type: "FeatureCollection",
//             features: features.map((d) => d.feature),
//         };

//         // Build a choropleth (wrapped with try/catch to reveal errors clearly)
//         let mapChart;
//         try {
//             mapChart = new Chart(ctx, {
//                 type: "choropleth",
//                 data: {
//                     labels: features.map((d) => d.name),
//                     datasets: [
//                         {
//                             label: "Nigeria States",
//                             data: features,
//                             outline: nigeriaFC,
//                             borderColor: "rgba(255,255,255,0.8)",
//                             borderWidth: 0.6,
//                             showOutline: true,
//                             backgroundColor: (ctx) => {
//                                 const cat = ctx.raw?.value || "low";
//                                 return (
//                                     colorByCategory[cat] || colorByCategory.low
//                                 );
//                             },
//                         },
//                     ],
//                 },
//                 options: {
//                     responsive: true,
//                     maintainAspectRatio: false,
//                     showOutline: true,
//                     plugins: {
//                         legend: {
//                             display: false,
//                         },
//                         // Colorbar disabled via scales.color (no plugin config required)
//                         tooltip: {
//                             backgroundColor: "rgba(14,58,63,0.92)",
//                             borderColor: "rgba(255,255,255,0.15)",
//                             borderWidth: 1,
//                             titleColor: "#fff",
//                             bodyColor: "#fff",
//                             callbacks: {
//                                 label: (ctx) => {
//                                     const state =
//                                         ctx.raw?.feature?.properties?.name ||
//                                         ctx.raw?.feature?.properties?.NAME_1 ||
//                                         ctx.label;
//                                     const cat = ctx.raw?.value;
//                                     const label = cat
//                                         ? cat.charAt(0).toUpperCase() +
//                                           cat.slice(1)
//                                         : "Low";
//                                     return `${state}: ${label} Risk`;
//                                 },
//                             },
//                         },
//                     },
//                     scales: {
//                         // Hide color scale and satisfy Chart.js with a valid position
//                         color: {
//                             type: "color",
//                             position: "left",
//                             display: false,
//                             legend: {
//                                 display: false,
//                             },
//                         },
//                         projection: {
//                             axis: "x",
//                             projection: "equalEarth",
//                             center: [8.6753, 9.082],
//                             padding: 8,
//                         },
//                     },
//                 },
//             });
//             console.log("Map initialized");
//         } catch (e) {
//             console.error("Map initialization error:", e);
//             alert("Map initialization error: " + (e?.message || e));
//             throw e;
//         }

//         // Simple export (PNG)
//         document.getElementById("exportBtn").addEventListener("click", () => {
//             const url = mapChart.toBase64Image();
//             const a = document.createElement("a");
//             a.href = url;
//             a.download = "nigeria-risk-heatmap.png";
//             a.click();
//         });
//     })
//     .catch((err) => {
//         console.warn("Primary GeoJSON load failed, trying relative path", err);
//         return fetch(geoUrlRel)
//             .then((r) => r.json())
//             .then((geojson) => {
//                 console.log("GeoJSON loaded via relative path", geoUrlRel);
//                 // Re-run the same initialization pipeline with the loaded geojson
//                 const features = (geojson.features || []).map((f) => {
//                     const n =
//                         f.properties.name ||
//                         f.properties.NAME_1 ||
//                         f.properties.state ||
//                         f.properties.admin1Name ||
//                         "";
//                     const cat = riskByState[n] || "low";
//                     return {
//                         feature: f,
//                         value: cat,
//                         name: n,
//                     };
//                 });

//                 if (!features.length) {
//                     console.error("No features found in GeoJSON (relative).");
//                     alert(
//                         "GeoJSON loaded but contains no features. Check file format."
//                     );
//                     return;
//                 }

//                 const ctx = document.getElementById("ngMap").getContext("2d");
//                 const nigeriaFC = {
//                     type: "FeatureCollection",
//                     features: features.map((d) => d.feature),
//                 };

//                 let mapChart;
//                 try {
//                     mapChart = new Chart(ctx, {
//                         type: "choropleth",
//                         data: {
//                             labels: features.map((d) => d.name),
//                             datasets: [
//                                 {
//                                     label: "Nigeria States",
//                                     data: features,
//                                     outline: nigeriaFC,
//                                     borderColor: "rgba(255,255,255,0.8)",
//                                     borderWidth: 0.6,
//                                     showOutline: true,
//                                     backgroundColor: (ctx) => {
//                                         const cat = ctx.raw?.value || "low";
//                                         return (
//                                             colorByCategory[cat] ||
//                                             colorByCategory.low
//                                         );
//                                     },
//                                 },
//                             ],
//                         },
//                         options: {
//                             responsive: true,
//                             maintainAspectRatio: false,
//                             showOutline: true,
//                             plugins: {
//                                 legend: {
//                                     display: false,
//                                 },
//                                 tooltip: {
//                                     backgroundColor: "rgba(14,58,63,0.92)",
//                                     borderColor: "rgba(255,255,255,0.15)",
//                                     borderWidth: 1,
//                                     titleColor: "#fff",
//                                     bodyColor: "#fff",
//                                     callbacks: {
//                                         label: (ctx) => {
//                                             const state =
//                                                 ctx.raw?.feature?.properties
//                                                     ?.name ||
//                                                 ctx.raw?.feature?.properties
//                                                     ?.NAME_1 ||
//                                                 ctx.label;
//                                             const cat = ctx.raw?.value;
//                                             const label = cat
//                                                 ? cat.charAt(0).toUpperCase() +
//                                                   cat.slice(1)
//                                                 : "Low";
//                                             return `${state}: ${label} Risk`;
//                                         },
//                                     },
//                                 },
//                             },
//                             scales: {
//                                 color: {
//                                     type: "color",
//                                     position: "left",
//                                     display: false,
//                                     legend: {
//                                         display: false,
//                                     },
//                                 },
//                                 projection: {
//                                     axis: "x",
//                                     projection: "equalEarth",
//                                     center: [8.6753, 9.082],
//                                     padding: 8,
//                                 },
//                             },
//                         },
//                     });
//                     console.log("Map initialized (relative path)");
//                 } catch (e) {
//                     console.error(
//                         "Map initialization error (relative path):",
//                         e
//                     );
//                     alert("Map initialization error: " + (e?.message || e));
//                     throw e;
//                 }

//                 document
//                     .getElementById("exportBtn")
//                     .addEventListener("click", () => {
//                         const url = mapChart.toBase64Image();
//                         const a = document.createElement("a");
//                         a.href = url;
//                         a.download = "nigeria-risk-heatmap.png";
//                         a.click();
//                     });
//             })
//             .catch((finalErr) => {
//                 console.error(
//                     "Could not load GeoJSON from either path:",
//                     finalErr
//                 );
//                 alert(
//                     "Could not load GeoJSON from either path. Confirm the server is running and the file exists at security/data/nigeria-state.geojson."
//                 );
//             });
//     });

document.addEventListener("DOMContentLoaded", function () {
    const regionNames = document.querySelectorAll(".region-name");
    const paths = document.querySelectorAll("path");

    regionNames.forEach((region) => {
        region.addEventListener("mouseenter", () => {
            const regionName = region.dataset.region;
            paths.forEach((path) => {
                if (path.getAttribute("data-name") === regionName) {
                    path.setAttribute("fill", "#008751");
                }
            });
        });

        region.addEventListener("mouseleave", () => {
            const regionName = region.dataset.region;
            paths.forEach((path) => {
                if (path.getAttribute("data-name") === regionName) {
                    path.setAttribute("fill", "#a2b0b3");
                }
            });
        });
    });
});
document.addEventListener("DOMContentLoaded", function () {
    var consent = sessionStorage.getItem("cookieConsent");
    var consentContainer = document.getElementById("cookieConsentContainer");

    if (consent === "accepted") {
        consentContainer.style.display = "none";
    } else {
        consentContainer.style.display = "flex"; // make sure it's visible if not accepted
    }

    document.getElementById("acceptCookieConsent").onclick = function () {
        consentContainer.style.display = "none";
        sessionStorage.setItem("cookieConsent", "accepted");
    };
});

function rejectCookies() {
    // Code to handle acceptance of only necessary cookies
    console.log("Only necessary cookies accepted.");
    document.getElementById("cookieConsentContainer").style.display = "none";
}
