<h3>Node Key</h3>
<table class="table table-bordered table-responsive">
    <thead>
    <tr>
        <th>Node</th>
        <th>Description</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><img src="{{ asset_url('images/help/graph/image001.png', 'core') }}" alt=""></td>
        <td>Dataset/collection</td>
    </tr>
    <tr>
        <td><img src="{{ asset_url('images/help/graph/image002.png', 'core') }}" alt=""></td>
        <td>Activity/program/grant</td>
    </tr>
    <tr>
        <td><img src="{{ asset_url('images/help/graph/image004.png', 'core') }}" alt=""></td>
        <td>Person</td>
    </tr>
    <tr>
        <td><img src="{{ asset_url('images/help/graph/image006.png', 'core') }}" alt=""></td>
        <td>Organisation/institution or group of people.</td>
    </tr>
    <tr>
        <td><img src="{{ asset_url('images/help/graph/image008.png', 'core') }}" alt=""></td>
        <td>Service</td>
    </tr>
    <tr>
        <td><img src="{{ asset_url('images/help/graph/image010.png', 'core') }}" alt=""></td>
        <td>Publication</td>
    </tr>
    <tr>
        <td><img src="{{ asset_url('images/help/graph/image012.png', 'core') }}" alt=""></td>
        <td>Website</td>
    </tr>
    <tr>
        <td><img src="{{ asset_url('images/help/graph/image013.png', 'core') }}" alt=""></td>
        <td>Primary node. This is the node representing the record being viewed in Research Data Australia. It is highlighted with a grey ring around it.</td>
    </tr>
    <tr>
        <td><img src="{{ asset_url('images/help/graph/image015.png', 'core') }}" alt=""></td>
        <td>Cluster node. Clusters are an aggregation of single nodes. A cluster node is created when there are more than 20 single nodes of the same type with the same relationship to the primary node. The count of nodes in the cluster is shown below the icon.</td>
    </tr>
    </tbody>
</table>
<p class="alert alert-info">
    Note: Nodes can represent objects which do not exist as a record in Research Data Australia .eg a website node.
</p>

<h3>Repositioning the graph</h3>
<p>To reposition the graph:</p>
<ol>
    <li>Click and hold your left mouse button down on any white area of the graph canvas.</li>
    <li>Drag the canvas to the desired position.</li>
    <li>Release the left mouse button.</li>
</ol>

<h3>Zooming in & out</h3>
<p>If you have a mouse with a scroll wheel you can use this to zoom the graph view in and out. When using this method the graph will zoom in and out from the location of the mouse pointer. You can use this feature to easily zoom into specific parts of the graph by positioning your pointer over the area of interest and then scrolling.
</p>
<p>If you don’t have a mouse with a scroll wheel you can use the zoom buttons to zoom the graph view in and out.  Use this method in combination with the ‘Repositioning the graph’ instructions to achieve the desired view.</p>

<h3>Isolating a node’s relationships</h3>
<p>To highlight a node’s relationships, simply hover your mouse pointer over the node. This will grey out any nodes which are not directly related. </p>
<img src="{{ asset_url('images/help/graph/image017.png', 'core') }}" alt="">

<h3>Expanding a node’s relationships</h3>
<p>
    Single non-primary nodes often have relationships to nodes that are not directly related to the primary node (the record being viewed in Research Data Australia) and are therefore not initially displayed in the graph view.  To load additional relationships, double click on a single node. A processing icon will be displayed while the system attempts to load any additional relationships. If no relationships exist for the clicked node the current graph view will simply refresh.
</p>

<h3>Viewing the type and title of a single node</h3>
<p>
    To view a node’s type and title, hover your mouse pointer over the node. This will display a tooltip with the values. Where a record exists in Research Data Australia or an external link is present the title for the node will be displayed as a hyperlink. Click the link to navigate to the object.
</p>
<img src="{{ asset_url('images/help/graph/image018.png', 'core') }}" alt="">

<h3>Viewing the records associated with clusters </h3>
<p>Where a cluster is formed from records that exist in Research Data Australia, the records in the cluster can be accessed via a search results listing.</p>

<p>To access the list of records:</p>
<ol>
    <li>Hover your mouse pointer over the cluster node. A tool tip will be displayed with the type of records in the cluster and the count . The count will be displayed as a hyperlink where the records exist in Research Data Australia.
        <img src="{{ asset_url('images/help/graph/image019.png', 'core') }}" alt=""></li>
    <li>Click the link to navigate to the search results listing where you can filter and access the records.</li>
</ol>

<h3>Viewing the relationships between nodes</h3>
<p>To view the relationships between 2 nodes, hover your mouse pointer over the connector. This will display a tooltip with the relationships.
</p>
<img src="{{ asset_url('images/help/graph/image020.png', 'core') }}" alt="">

