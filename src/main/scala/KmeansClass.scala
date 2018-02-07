import org.apache.spark.mllib.clustering.KMeans
import org.apache.spark.mllib.linalg.Vectors
import org.apache.spark.rdd.RDD
import org.apache.spark.{SparkConf, SparkContext}

import scala.util.Try

class KmeansClass{
  def kmeansFunction(sc:SparkContext):(Array[org.apache.spark.mllib.linalg.Vector],RDD[((String,String), Int)], RDD[(Int,Int)]) = {

    val c_path_in = "crimes.csv"
    val c_path_out ="q3"
    val n_clusters = 9
    val n_max_iteration = 20

    /* Load and parse the data */
    val r_csv = sc.textFile(c_path_in);

    /* Find the headers */
    //val c_header = r_csv.first;

    /* Remove the header */
    //val r_data = r_csv.filter(_(0) != c_header(0));
    val r_data = r_csv;

    val cleanData = r_data
      .map(line => {
        val splitedLine = line.split(",")
        val splittedArrayLength = splitedLine.length
        (splitedLine(splittedArrayLength-4),splitedLine(splittedArrayLength-3))
      })
      .filter(t => (t._1.nonEmpty
        &&t._2.nonEmpty
        &&Try(t._1.toDouble).isSuccess
        &&Try(t._2.toDouble).isSuccess))

    val r_parsedData = cleanData.map(s => Vectors.dense(s._1.toDouble, s._2.toDouble)).cache()

    /* Cluster the data into two classes using KMeans */
    val x_clusters = KMeans.train(r_parsedData, n_clusters, n_max_iteration)

    /* Evaluate clustering by computing Within Sum of Squared Errors */
    val n_wsse = x_clusters.computeCost(r_parsedData)

    println("Within Sum of Squared Errors = " + n_wsse)
    println("Cluster centers = ")

    val centers = x_clusters.clusterCenters

    val l_cluster_assignment = x_clusters.predict(r_parsedData)

    val clusters_count = l_cluster_assignment
      .map(t => (t, 1))
      .reduceByKey(_ + _)

    val finalAffectation = cleanData.cartesian(l_cluster_assignment)

    //finalAffectation.saveAsTextFile(c_path_out)

    return (centers, finalAffectation, clusters_count)
  }
}