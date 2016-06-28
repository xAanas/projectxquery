<?php
    include_once("../search/arc/ARC2.php");
    /* Configure the app to use DBPedia. */
    $dbpconfig = array(
    "remote_store_endpoint" => "http://fr.dbpedia.org/sparql",
    );
    $store = ARC2::getRemoteStore($dbpconfig);


    $q = '
    PREFIX ent: <http://s.opencalais.com/1/type/em/e/>
    PREFIX t: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX evn: <http://s.opencalais.com/1/type/em/r/> 
    PREFIX pred: <http://s.opencalais.com/1/pred/>
    SELECT  distinct  ?s ?v ?o WHERE {

            ?s1 pred:relationsubject ?s_uri.
            ?s_uri pred:name ?s .
            ?s1 pred:relationobject ?o .
            ?s1 pred:verb ?v 

    }';
    $q2='prefix db-owl: <http://dbpedia.org/ontology/>
 select ?t where {<http://fr.dbpedia.org/resource/Paris> ?t rdf:type http://www.w3.org/2002/07/owl#Thing
 }';
    
    $q3='select * where {<http://fr.dbpedia.org/resource/Paris> ?r ?p}';
    $q4='prefix db-owl: <http://dbpedia.org/ontology/>
        prefix ow:<http://www.w3.org/2002/07/>
        select ?k where {
        ?k ow:owl <http://fr.dbpedia.org/resource/Île-de-France>
        }';

    $q6='select * where {
    ?ville rdfs:label "Île-de-France"@fr
    ?categorie rdf:type 
    }';
    $q7="
  PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>
  PREFIX owl: <http://www.w3.org/2002/07/owl#>
  PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
  PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
  PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
  PREFIX foaf: <http://xmlns.com/foaf/0.1/>
  PREFIX dc: <http://purl.org/dc/elements/1.1/>
  PREFIX g: <http://dbpedia.org/resource/>
  PREFIX dbpedia2: <http://dbpedia.org/property/>
  PREFIX dbpedia: <http://dbpedia.org/>
  PREFIX dbpprop: <http://dbpedia.org/property/> 
  SELECT * WHERE {
        GRAPH ?g {
            {
            ?uri rdfs:label ?label .
            } UNION {
            ?uri foaf:name ?label .
            } UNION {
            ?uri rdfs:comment ?label .
            }
            OPTIONAL {
            ?uri a ?t .
            }
            FILTER regex(?label, 'Obama', 'i')
        }
  }
  ORDER BY ?uri limit 5'";
    $q8='  PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>
        PREFIX dbpedia: <http://dbpedia.org/resource/>
        PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
        PREFIX foaf: <http://xmlns.com/foaf/0.1/>
        PREFIX dbpprop: <http://dbpedia.org/property/> 
        SELECT  ?oeuvre ?o ?oo
        WHERE {
            ?oeuvre rdfs:label "Les Misérables"@en.            
            ?oeuvre dbpedia-owl:author ?o.
            ?o rdfs:label ?oo.
            FILTER(LANGMATCHES(LANG(?oo), "en"))

        }';
    $q9='PREFIX foaf: <http://xmlns.com/foaf/0.1/> 
        select distinct ?predicat 
        where 
        {
        ?sujet a foaf:Person.
        ?sujet rdfs:label ?oo.
        FILTER(LANGMATCHES(LANG(?oo), "en"))
        } limit 100';
    $q10='  PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>
        PREFIX dbpedia: <http://dbpedia.org/resource/>
        PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
        PREFIX foaf: <http://xmlns.com/foaf/0.1/>
        PREFIX dbpprop: <http://dbpedia.org/property/> 
        PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
        SELECT  ?oo
        WHERE {
            ?subject rdfs:label "Victor Hugo"@en.
            ?subject rdf:type ?object.
            ?object rdfs:label ?oo.
            FILTER(LANGMATCHES(LANG(?oo), "en"))

        }';
    
    $rows = $store->query($q10, 'rows');
    var_dump($rows);exit();
    
?>