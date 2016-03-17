function deg2rad(deg) {
     
    "use strict";
    
    return deg * (Math.PI / 180);
}

function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    "use strict";
    
    var R, dLat, dLon, a, c, d;
    
    R = 6371; // Radius of the earth in km
    dLat = deg2rad(lat2 - lat1);  // deg2rad below
    dLon = deg2rad(lon2 - lon1);
    a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    d = R * c; // Distance in km
    
    return d;
}

function translate(point) {
    
    "use strict";
    
    return [point[0] - (terrainSize / 2), (terrainSize / 2) - point[1]];
}

// @see http://blog.thematicmapping.org/2013/11/showing-gps-tracks-in-3d-with-threejs.html
function latlon_to_xy(lat, lon) {
  
    "use strict";
    
    var latitude, longitude, coord;
    
    latitude = parseFloat(lat);
    longitude = parseFloat(lon);
    coord = translate(projection([longitude, latitude]));

    return coord;
}     

        
// Draw debug axes
function draw_debug_axes() {
   
    "use strict";
    
    var debugaxis = function (axisLength) {
        //Shorten the vertex function
        function v(x, y, z) {
            return new THREE.Vector3(x, y, z);
        }
        
        //Create axis (point1, point2, colour)
        function createAxis(p1, p2, color) {
            var line, lineGeometry = new THREE.Geometry(),
                lineMat = new THREE.LineBasicMaterial({color: color, lineWidth: 1});
            lineGeometry.vertices.push(p1, p2);
            line = new THREE.Line(lineGeometry, lineMat);
            scene.add(line);
        }
        
        createAxis(v(-axisLength, 0, 0), v(axisLength, 0, 0), 0xFF0000);
        createAxis(v(0, -axisLength, 0), v(0, axisLength, 0), 0x00FF00);
        createAxis(v(0, 0, -axisLength), v(0, 0, axisLength), 0x0000FF);
    };
        
    debugaxis(100);
}
        
function createLabel(text, x, y, z, size, color, backGroundColor) {
    
    "use strict";
    
    var canvas, context, textWidth, texture, material, mesh;

    canvas = document.createElement("canvas");

    context = canvas.getContext("2d");
    context.font = size + "pt Arial";

    textWidth = context.measureText(text).width;

    canvas.width = textWidth + 15;
    canvas.height = size;
    context = canvas.getContext("2d");
    context.font = size + "pt Arial";

    if (backGroundColor) {
        context.fillStyle = backGroundColor;
        context.fillRect(
            canvas.width / 2 - textWidth / 2 - backgroundMargin / 2,
            canvas.height / 2 - size / 2 - +backgroundMargin / 2,
            textWidth + backgroundMargin,
            size + backgroundMargin
        );
    }

    context.textAlign = "right";
    context.textBaseline = "middle";
    context.fillStyle = color;
    context.fillText(text, canvas.width / 2, canvas.height / 2);

    texture = new THREE.Texture(canvas);
    texture.needsUpdate = true;

    material = new THREE.MeshBasicMaterial({
        map : texture,
        depthTest: false,
        transparent: true
    });

    mesh = new THREE.Mesh(new THREE.PlaneGeometry(canvas.width, canvas.height), material);
    // mesh.overdraw = true;
    mesh.doubleSided = true;
    mesh.position.x = x - canvas.width;
    mesh.position.y = y - canvas.height;
    mesh.position.z = z;
    mesh.lookAt(camera.position);

    return mesh;
}
        
function draw_line(lat_1, lon_1, lat_2, lon_2) {
    
    "use strict";
    
    var start, center, end, distance, spline,
        line_material, line_geometry, splinePoints, line, i;
    
    start = latlon_to_xy(lat_1, lon_1);
    center = latlon_to_xy((lat_1 + lat_2) / 2, (lon_1 + lon_2) / 2);
    end = latlon_to_xy(lat_2, lon_2);
        
    distance = getDistanceFromLatLonInKm(lat_1, lon_1, lat_2, lon_2);
       
    // Draw a curve
    // @see http://stackoverflow.com/questions/11165607/creating-a-spline-curve-between-2-points-in-three-js
    spline = new THREE.SplineCurve3([
        new THREE.Vector3(start[0], 0, -start[1]),
        new THREE.Vector3(center[0], 5 * distance * 0.5, -center[1]),
        new THREE.Vector3(end[0], 0, -end[1])
    ]);
    
    line_material = new THREE.LineBasicMaterial({
        color: 0xFFB87A,
        opacity: 0.8,
        transparent: true
    });
    
    line_geometry = new THREE.Geometry();
    splinePoints = spline.getPoints(curve_num_points);
    
    for (i = 0; i < splinePoints.length; i++) {
        line_geometry.vertices.push(splinePoints[i]);
    }
    
    line = new THREE.Line(line_geometry, line_material);
    scene.add(line);
    
    // Draw particles
    draw_particle(lat_1, lon_1, particleMaterial_primary);
    draw_particle(lat_2, lon_2, particleMaterial_secondary);
}